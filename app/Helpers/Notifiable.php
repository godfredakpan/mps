<?php

namespace App\Helpers;

use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Notifications\Notifiable as BaseNotifiable;

trait Notifiable
{
    use BaseNotifiable;

    public function notify($instance)
    {
        try {
            app(Dispatcher::class)->send($this, $instance);
        } catch (\Exception $e) {
            info('Notification email has been failed. ' . $e->getMessage());
        }
    }
}
