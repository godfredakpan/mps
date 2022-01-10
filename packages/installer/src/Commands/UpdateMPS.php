<?php

namespace Tecdiary\Installer\Commands;

use Exception;
use ZipArchive;
use Carbon\Carbon;
use Modules\MPS\Models\User;
use Illuminate\Console\Command;
use Modules\MPS\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Modules\MPS\Notifications\AppNotification;

class UpdateMPS extends Command
{
    public $hidden = true;

    protected $composer = 0;

    protected $description = 'Update module (MPS) and packages';

    protected $signature = 'mps:update {--now} {--c} {--force}';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        set_time_limit(1200);
        if (!$this->option('force') && !$this->confirm('Have you backup the files & database?')) {
            $this->line('Please backup your files & database then try again.');
            return 1;
        }

        $this->composer  = $this->option('c');
        $this->updateNow = $this->option('now');
        if (!$this->composer) {
            if ($this->confirm('Do you want to update composer packages too?')) {
                $this->composer = 1;
            }
        }

        return $this->update();
    }

    private function notifySuperAdmins(array $data, $email = false)
    {
        $users = User::role('super')->get();
        if ($users && $users->isNotEmpty()) {
            foreach ($users as $user) {
                $user->notify(new AppNotification($data, $user, $email));
            }
        }
    }

    private function update()
    {
        $now  = now()->startOfDay();
        $time = mps_config('auto_update_time', true);
        $next = isset($time->checked_at) ? Carbon::parse($time->checked_at)->addDays(6)->startOfDay() : now()->subDay()->startOfDay();
        if ($this->updateNow || $now->greaterThan($next)) {
            $keys = json_decode(Storage::disk('local')->get('keys.json'), true);
            if ($keys['mps']) {
                if ($time) {
                    $time->checked_at = $next->toDateString();
                }
                Artisan::call('down');
                $this->line('Checking if update available, please wait...');
                try {
                    $url = 'http://be.tecdiary.net/api/v1/updates/check';
                    // $url   = 'http://be-tec-net.test/api/v1/updates/check';
                    $comp  = json_decode(file_get_contents(base_path('composer.json')), true);
                    $pdata = ['ver' => $comp['version'], 'key' => $keys['mps'], 'dom' => url('/')];
                    $resp  = Http::withHeaders(['Accept' => 'application/json'])->withOptions(['verify' => false])->get($url, $pdata);

                    $data = $resp->json();
                    if ($resp->successful() && $data && $data['success']) {
                        if (!empty($data['updates'])) {
                            $this->line('Update available, installing now...');
                            $this->updateModule($data['updates']);

                            if ($keys['shop']) {
                                $pdata = ['ver' => $comp['version'], 'key' => $keys['shop'], 'dom' => url('/')];
                                $mres  = Http::withHeaders(['Accept' => 'application/json'])->withOptions(['verify' => false])->get($url, $pdata);
                                $mdata = $mres->json();
                                if ($mres->successful() && $mdata && $mdata['success']) {
                                    $this->updateModule($mdata['updates']);
                                }
                            }

                            $this->line('Running migrations now...');
                            Artisan::call('migrate --force');
                            $this->line(Artisan::output());

                            if ($this->composer) {
                                $this->line('Updating the composer packages now, this could take few minutes. Please wait...');
                                $exitCode = Artisan::call('composer:update');
                                if ($exitCode) {
                                    Log::error('Failed to update composer packages, please run `composer install` manually.');
                                    $this->error('Failed to update composer packages, please run `composer install` manually.');
                                    $this->notifySuperAdmins([
                                        'title'       => 'Composer command failed!',
                                        'description' => 'Failed to update composer packages, please run `composer install` manually.',
                                    ], true);
                                }
                            }

                            Setting::updateOrCreate(['mps_key' => 'auto_update_time'], ['mps_value' => json_encode($time)]);
                            $this->info('Update completed! you are using the latest version now.');
                            $this->notifySuperAdmins([
                                'title'       => 'Application Updated',
                                'description' => 'Application has been updated to latest version ' . ($update['version'] ?? ''),
                            ]);
                        } else {
                            $this->info($data['message'] ?? 'You are using the latest version.');
                        }
                    } else {
                        Setting::updateOrCreate(['mps_key' => 'auto_update_time'], ['mps_value' => json_encode($time)]);
                        if ($resp->status() == 422) {
                            Log::error($data['message'], ['errors' => $data['errors'] ?? []]);
                            $this->notifySuperAdmins([
                                'title'       => 'Application Update Failed!',
                                'description' => $data['message'] . ' Please contact developer. Thank you',
                            ]);
                            $this->error($data['message'] . ' Please contact developer. Thank you');
                        } elseif ($resp->status() == 429) {
                            $this->notifySuperAdmins([
                                'title'       => 'Application Update Failed!',
                                'description' => 'Too many requests, please try again tomorrow. Thank you',
                            ]);
                            $this->error('Too many requests, please try again tomorrow. Thank you');
                        } else {
                            Log::error('The update check request has been failed.');
                            $this->notifySuperAdmins([
                                'title'       => 'Application Update Failed!',
                                'description' => 'The update check request has been failed.',
                            ]);
                            $this->error('The update check request has been failed.');
                        }
                    }
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                    $this->line('Error ocurred!');
                    $this->line('Unable to connect to update server.');
                    $this->notifySuperAdmins([
                        'title'       => 'Application Update Failed!',
                        'description' => 'Unable to connect to update server.',
                    ]);
                    Artisan::call('up');
                    exit();
                }
            } else {
                $this->error('Application key is not set, please contact developer with your license key. Thank you');
            }
            Artisan::call('up');
        } else {
            $this->line('Your settings don\'t allow to update the item at this time. Please use --now flag if you wish to update now.');
        }
    }

    private function updateModule($updates)
    {
        if ($updates) {
            foreach ($updates as $update) {
                try {
                    $this->line('Updating to version ' . $update['version'] . ', please wait...');
                    $path = Storage::disk('local')->putFileAs('updates', $update['url'], $update['filename']);
                    if (Storage::disk('local')->exists($path)) {
                        $filepath = Storage::disk('local')->path($path);
                        try {
                            $zip = new ZipArchive();
                            if ($zip->open($filepath) === true) {
                                $zip->extractTo(base_path());
                                $zip->close();
                                Storage::disk('local')->delete($path);
                                $this->info('Updated to version ' . $update['version']);
                            } else {
                                $this->error('Failed to extract the update file ' . $path);
                                Storage::disk('local')->delete($path);
                                Artisan::call('up');
                                return 1;
                            }
                        } catch (Exception $e) {
                            $this->error($e->getMessage());
                            $this->notifySuperAdmins([
                                'title'       => 'Application Update Failed!',
                                'description' => 'Application failed to extract the download file',
                            ]);
                            Artisan::call('up');
                            return 1;
                        }
                    } else {
                        $this->error('Failed to copy the update file ' . $path);
                        $this->notifySuperAdmins([
                            'title'       => 'Application Update Failed!',
                            'description' => 'Failed to copy the update file',
                        ]);
                        Artisan::call('up');
                        return 1;
                    }
                } catch (Exception $e) {
                    $this->error($e->getMessage());
                    $this->line('Exiting the update...');
                    $this->line('Please try again and if still same, contact developer. Thank you');
                    $this->notifySuperAdmins([
                        'title'       => 'Application Update Failed!',
                        'description' => 'Application update has been failed with error ' . $e->getMessage(),
                    ]);
                    Artisan::call('up');
                    return 1;
                }
            }
        }
    }
}
