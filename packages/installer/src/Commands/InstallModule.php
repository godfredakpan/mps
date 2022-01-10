<?php

namespace Tecdiary\Installer\Commands;

use App\User;
use Exception;
use ZipArchive;
use Ramsey\Uuid\Uuid;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Modules\MPS\Notifications\AppNotification;

class InstallModule extends Command
{
    public $hidden = true;

    protected $description = 'Install module for Modern Point of Sale Solution';

    protected $signature = 'mps:install {module?} {license?}';

    public function handle()
    {
        $modules = ['Shop'];
        $module  = $this->argument('module');
        $license = $this->argument('license');

        if (!$module) {
            $module = $this->choice('Please select module', ['Shop'], 0);
        }
        if (!$license) {
            $license = $this->ask('Please provide license key');
        }
        $isValid = Uuid::isValid($license);
        if (!$isValid) {
            $this->ERROR('Invalid license key');
            $license = $this->ask('Please provide correct license key');
            $isValid = Uuid::isValid($license);
            if (!$isValid) {
                $this->ERROR('Invalid license key');
            }
        }
        if ($module && $license && $isValid) {
            // $this->line("Installing {$module} module");
            return $this->install($module, $license);
        }
    }

    private function install($module, $license)
    {
        set_time_limit(1200);
        try {
            $keys = json_decode(Storage::disk('local')->get('keys.json'), true);
            if ($keys['mps']) {
                $this->callSilently('down');
                try {
                    $comp  = json_decode(file_get_contents(base_path('composer.json')), true);
                    $postd = [
                        'item_id'  => 12,
                        'module'   => $module,
                        'key'      => $license,
                        'domain'   => url('/'),
                        'main_key' => $keys['mps'],
                        'version'  => $comp['version'],
                    ];
                    // $resp = Http::withHeaders(['Accept' => 'application/json'])->post('http://be-tec-net.test/api/v1/module', $postd);
                    $resp = Http::withHeaders(['Accept' => 'application/json'])->withOptions(['verify' => false])->post('https://be.tecdiary.net/api/v1/module', $postd);

                    $data = $resp->json();
                    if ($resp->successful() && $data && $data['success']) {
                        try {
                            $this->line('Please wait...');
                            $path = storage_path('app/' . $data['filename']);
                            file_put_contents($path, file_get_contents($data['url']));
                            if (file_exists($path)) {
                                try {
                                    $zip = new ZipArchive();
                                    if ($zip->open($path) === true) {
                                        $zip->extractTo(base_path());
                                        $zip->close();
                                        unlink($path);
                                        $this->info('Files copied.');
                                    } else {
                                        $this->error('Failed to extract the file.');
                                        $this->line('Exiting the installation...');
                                        $this->callSilently('up');
                                        unlink($path);
                                        return 1;
                                    }
                                } catch (Exception $e) {
                                    $this->error($e->getMessage());
                                    $this->notifySuperAdmins([
                                        'title'       => 'Module installation failed!',
                                        'description' => ' Failed to extract the file.',
                                    ]);
                                    $this->callSilently('up');
                                    return 1;
                                }
                            } else {
                                $this->error('Failed to copy the downloaded file ' . $path);
                                $this->notifySuperAdmins([
                                    'title'       => 'Module installation failed!',
                                    'description' => 'Failed to copy the file.',
                                ]);
                            }
                        } catch (Exception $e) {
                            $this->error($e->getMessage());
                            $this->line('Exiting the installation...');
                            $this->line('Please try again and if still same, contact developer. Thank you');
                            $this->notifySuperAdmins([
                                'title'       => 'Module installation failed!',
                                'description' => 'Module installation has been failed with error ' . $e->getMessage(),
                            ]);
                            $this->callSilently('up');
                            return 1;
                        }

                        DB::unprepared(DB::raw($data['sql']));
                        $this->line('Database schema updated.');

                        (Module::find($module))->enable();
                        $this->line('Enabled module.');

                        $keys[mb_strtolower($module)] = $license;
                        Storage::disk('local')->put('keys.json', json_encode($keys, JSON_PRETTY_PRINT));

                        // $this->line('Updating the packages now, this could take few minutes. Please wait...');
                        // $this->callSilently('composer:update');
                        $this->info('Installation completed! Thank you!');
                        $this->notifySuperAdmins([
                            'title'       => 'Module installation completed',
                            'description' => 'Module installation has been completed.',
                        ]);
                    } else {
                        if ($resp->status() == 422) {
                            Log::error($data['message'], ['errors' => $data['errors'] ?? []]);
                            $this->notifySuperAdmins([
                                'title'       => 'Module installation failed!',
                                'description' => $data['message'],
                            ]);
                            $this->error($data['message']);
                        } elseif ($resp->status() == 429) {
                            $this->notifySuperAdmins([
                                'title'       => 'Module installation failed!',
                                'description' => 'Too many requests, please try again later. Thank you',
                            ]);
                            $this->error('Too many requests, please try again later. Thank you');
                        } else {
                            Log::error('The installation request has been failed with unknown server error.');
                            $this->notifySuperAdmins([
                                'title'       => 'Module installation failed!',
                                'description' => 'The installation request has been failed with unknown error.',
                            ]);
                            $this->error('The installation request has been failed with unknown server error.');
                        }
                        $this->callSilently('up');
                        return 1;
                    }
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                    $this->line('Error ocurred!');
                    $this->line('Unable to connect to install server.');
                    $this->notifySuperAdmins([
                        'title'       => 'Module installation failed!',
                        'description' => 'Unable to connect to install server.',
                    ]);
                    $this->callSilently('up');
                    return 1;
                }
            } else {
                $this->error('Application key is not set, please contact developer with your license key of Modern Point of Sale Solution. Thank you');
            }
            $this->callSilently('up');
        } catch (Exception $e) {
            $this->error($e->getMessage());
            $this->notifySuperAdmins([
                'title'       => 'Module installation failed!',
                'description' => $e->getMessage(),
            ]);
            return 1;
        }
    }

    private function notifySuperAdmins(array $data)
    {
        $users = User::role('super')->get();
        if ($users && $users->isNotEmpty()) {
            foreach ($users as $user) {
                $user->notify(new AppNotification($data, $user));
            }
        }
    }
}
