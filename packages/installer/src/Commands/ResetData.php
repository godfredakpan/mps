<?php

namespace Tecdiary\Installer\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ResetData extends Command
{
    public $hidden = true;

    protected $description = 'Truncate all the tables in database';

    protected $signature = 'data:reset {--force} {--seed}';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        if ($this->option('force')) {
            if ($this->confirm('It will delete all the data from your database. Do you have Developer license?')) {
                $this->migrateFresh($this->option('seed'));
            }
        } elseif (demo()) {
            $this->migrateFresh(true);
        } else {
            $this->line('I do not have anything to do! Do I?');
        }
    }

    private function migrateFresh($seed)
    {
        $this->line('Migration started...');
        set_time_limit(300);
        Artisan::call('migrate:fresh', [
            '--force' => true,
            '--seed'  => $seed,
        ]);
    }
}
