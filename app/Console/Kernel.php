<?php

namespace App\Console;

use Nwidart\Modules\Facades\Module;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [];

    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

    protected function schedule(Schedule $schedule)
    {
        // MPS Module
        if (Module::has('MPS')) {
            if ((Module::find('MPS'))->isEnabled()) {
                $schedule->command('reference:reset')->daily()->withoutOverlapping();
                $schedule->command('recurring:sales')->daily()->withoutOverlapping();
                $schedule->command('payment:reminder')->daily()->withoutOverlapping();
                $schedule->command('recurring:expenses')->daily()->withoutOverlapping();
                $schedule->command('staff:salaries')->monthlyOn(1, '1:00')->withoutOverlapping();
                if (!demo()) {
                    $autoUpdate = mps_config('auto_update');
                    if ($autoUpdate) {
                        $opFilePath = public_path('vendor/update');
                        $updateTime = mps_config('auto_update_time');
                        $schedule->command('mps:update --c --force')->weekly()->{$updateTime['day']()}->between($updateTime['time'])->appendOutputTo($opFilePath);
                    }
                }
            }
        }
        if (Module::has('Shop')) {
            if ((Module::find('Shop'))->isEnabled()) {
                $schedule->command('currency:rate')->daily()->withoutOverlapping();
            }
        }
        if (demo()) {
            $schedule->command('data:reset')->twiceDaily(1, 13)->withoutOverlapping(5);
        }
        $schedule->command('backup:clean')->dailyAt('01:00');
        $schedule->command('logcleaner:run')->dailyAt('01:00');
        $schedule->command('activitylog:clean')->dailyAt('23:00');
        $schedule->command('backup:run --only-db')->dailyAt('02:00');
        $schedule->command('notifications:clear')->daily()->at('23:15');
    }
}
