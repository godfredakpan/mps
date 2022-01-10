<?php

namespace Modules\MPS\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Log;

class EndTimeClock
{
    public function failed(Logout $event, $exception)
    {
        Log::error('EndTimeClock End time clock failed!', ['user' => $event->user, 'Error' => $exception]);
    }

    public function handle(Logout $event)
    {
        if ($event->user) {
            if (usermeta($event->user->id, 'clock_in') == 'login') {
                $user = user($event->user->id);
                $user->disableLogging();
                $time_clock = $user->timeClocks()->current()->first();
                if ($time_clock) {
                    $user->logActivity(__('Automatically clocking out user with logout'));
                    $time_clock->update(['out_time' => now()]);
                }
            }
        }
    }
}
