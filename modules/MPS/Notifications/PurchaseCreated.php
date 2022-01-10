<?php

namespace Modules\MPS\Notifications;

use Illuminate\Bus\Queueable;
use Modules\MPS\Models\Purchase;
use Illuminate\Support\Facades\URL;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PurchaseCreated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $attach;

    protected $purchase;

    public function __construct(Purchase $purchase, $attach = true)
    {
        $this->purchase = $purchase;
        $this->attach   = $attach;
    }

    public function toMail($notifiable)
    {
        $url = URL::signedRoute('order', ['act' => 'purchase', 'hash' => $this->purchase->hash]);

        $mail = (new MailMessage())
            ->greeting(__('mps::mail.hello', ['name' => $this->purchase->supplier->name]))
            ->subject(__('mps::mail.purchase_created_subject'))
            ->line(__('mps::mail.purchase_opening_line', ['reference' => $this->purchase->reference]))
            ->action(__('mps::mail.purchase_button_text'), $url)
            ->line(__('mps::mail.purchase_closing_line'));

        if ($this->attach) {
            try {
                $settings = json_decode(mps_config(), true);
                unset($settings['svg_string'], $settings['json_string']);
                // $this->purchase->attributes = extra_attributes('purchase');
                $this->purchase->loadMissing(['location', 'supplier', 'payments', 'items' => fn ($q) => $q->withAll()]);
                $pdf = app()->make('dompdf.wrapper');
                $pdf->loadView('mps::pdf.purchase', ['settings' => $settings, 'purchase' => $this->purchase]);
                $mail->attachData($pdf->output(), $this->purchase->id . '.pdf', ['mime' => 'application/pdf']);
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
