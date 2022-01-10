<?php

namespace Tecdiary\Installer\Commands;

use Exception;
use Illuminate\Console\Command;
use Composer\Console\Application;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Input\ArrayInput;

class ComposerUpdate extends Command
{
    public $hidden = true;

    protected $description = 'Update all composer packages to latest supported versions.';

    protected $signature = 'composer:update';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        set_time_limit(1200);
        return $this->update();
    }

    private function update()
    {
        try {
            chdir(base_path());
            putenv('COMPOSER_MEMORY_LIMIT=2G');
            putenv('COMPOSER_HOME=' . base_path('vendor/bin/composer'));

            $this->info(now()->format('Y-m-d H:i:s') . ': Updating packages! this may take few minutes, please wait...');
            $application = new Application();
            $application->setAutoExit(false);
            $application->run(new ArrayInput(['command' => 'update', '--no-cache' => true]));
            $this->info(now()->format('Y-m-d H:i:s') . ': Packages are updated!');
            return 0;
        } catch (Exception $e) {
            Log::error('Failed to run `php artisan composer:update` please run `composer install` manually.', ['error' => $e->getMessage()]);
            $this->error(now()->format('Y-m-d H:i:s') . ': ' . $e->getMessage());
            return 1;
        }
    }
}
