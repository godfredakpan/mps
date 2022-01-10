<?php

namespace Modules\MPS\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\MPS\Events\RegisterRecordEvent;

class TimeClockWithRegister
{
    public function failed(RegisterRecordEvent $event, $exception)
    {
        Log::error('TimeClockWithRegister failed!', ['Error' => $exception, 'user' => $event->user, 'record' => $event->register_record]);
    }

    public function handle(RegisterRecordEvent $event)
    {
        if (usermeta($event->user->id, 'clock_in') == 'register') {
            $user = user($event->user->id);
            $user->disableLogging();
            $time_clock = $user->timeClocks()->current()->first();
            if ($time_clock) {
                $user->logActivity(__('Automatically clocking out user with close register'));
                $time_clock->update(['out_time' => now()]);
            } else {
                $user->logActivity(__('Automatically clocking in user with register'));
                $time_clock = $user->timeClocks()->create([
                    'in_time'     => now(),
                    'details'     => __('Auto clocked in with register'),
                    'location_id' => session('location_id', $user->location_id),
                    'rate'        => optional($user->meta()->where('meta_key', 'hourly_rate')->first())->meta_value ?? 0,
                ]);
            }
        }
    }
}
