<?php

namespace Modules\MPS\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\URL;
use Modules\MPS\Models\ReturnOrder;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ReturnOrderCreated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $attach;

    protected $return_order;

    public function __construct(ReturnOrder $return_order, $attach = true)
    {
        $this->return_order = $return_order;
        $this->attach       = $attach;
    }

    public function toMail($notifiable)
    {
        $url = URL::signedRoute('order', ['act' => 'return_order', 'hash' => $this->return_order->hash]);

        $mail = (new MailMessage())
            ->greeting(__('mps::mail.hello', ['name' => $this->return_order->customer->name]))
            ->subject(__('mps::mail.return_order_created_subject'))
            ->line(__('mps::mail.return_order_opening_line', ['reference' => $this->return_order->reference]))
            ->action(__('mps::mail.return_order_button_text'), $url)
            ->line(__('mps::mail.return_order_closing_line'));

        if ($this->attach) {
            try {
                $settings = json_decode(mps_config(), true);
                unset($settings['svg_string'], $settings['json_string']);
                // $this->return_order->attributes = extra_attributes('return_order');
                $this->return_order->loadMissing([
                    'location', 'customer', 'supplier', 'payments', 'items' => fn ($q) => $q->withAll(),
                ]);
                $pdf = app()->make('dompdf.wrapper');
                $pdf->loadView('mps::pdf.return_order', ['settings' => $settings, 'return_order' => $this->return_order]);
                $mail->attachData($pdf->output(), $this->return_order->id . '.pdf', ['mime' => 'application/pdf']);
            } catch (\Exception $e) {
                info('PDF generation failed!', ['error' => $e->getMessage()]);
            }
        }

        return $mail;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }
}
