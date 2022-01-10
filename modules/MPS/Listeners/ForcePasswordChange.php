<?php

namespace Modules\MPS\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Log;

class ForcePasswordChange
{
    public function failed(Login $event, $exception)
    {
        Log::error('ForcePasswordChange Force to change password failed!', ['user' => $event->user, 'Error' => $exception]);
    }

    public function handle(Login $event)
    {
        if (usermeta($event->user->id, 'first_login')) {
            // log_activity('User is forces to change password', ['user' => $user], $user);
            // TODO force password reset
        }
    }
}
