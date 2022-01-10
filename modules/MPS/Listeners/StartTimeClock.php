<?php

namespace Modules\MPS\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Log;

class StartTimeClock
{
    public function failed(Login $event, $exception)
    {
        Log::error('StartTimeClock Start time clock failed!', ['user' => $event->user, 'Error' => $exception]);
    }

    public function handle(Login $event)
    {
        if (usermeta($event->user->id, 'clock_in') == 'login') {
            $user = user($event->user->id);
            $user->disableLogging();
            $time_clock = $user->timeClocks()->current()->first();
            if (!$time_clock) {
                $user->logActivity(__('Automatically clocking in user with login'));
                $time_clock = $user->timeClocks()->create([
                    'in_time'     => now(),
                    'details'     => __('Auto clocked in with login'),
                    'location_id' => session('location_id', $user->location_id),
                    'rate'        => optional($user->meta()->where('meta_key', 'hourly_rate')->first())->meta_value ?? 0,
                ]);
            }
        }
    }
}
