<?php

namespace Modules\MPS\Notifications;

use Modules\MPS\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class AppNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $data;

    protected $email;

    protected $user;

    public function __construct(array $data, User $user, $email = false)
    {
        $this->data  = $data;
        $this->user  = $user;
        $this->email = $email;
    }

    public function toArray($notifiable)
    {
        return $this->data;
    }

    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->greeting(__('mps::mail.hello', ['name' => $this->user->name]))
            ->subject(__('mps::mail.auto_update_failed'))
            ->line(__('mps::mail.update_failed_line', ['domain' => url('/')]))
            ->line(__('mps::mail.update_failed_closing_line'));
    }

    public function via($notifiable)
    {
        return $this->email ? ['database', 'mail'] : ['database'];
    }
}
