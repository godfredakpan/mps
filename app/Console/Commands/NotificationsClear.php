<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Notifications\DatabaseNotification;

class NotificationsClear extends Command
{
    protected $description = 'Delete all notifications older than 30 days.';

    protected $signature = 'notifications:clear';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        DatabaseNotification::where('created_at', '<=', now()->subDays(30))->delete();
        $this->info('Deleted all notifications older than 30 days');
    }
}
