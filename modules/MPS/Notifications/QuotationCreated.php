<?php

namespace Modules\MPS\Notifications;

use Illuminate\Bus\Queueable;
use Modules\MPS\Models\Quotation;
use Illuminate\Support\Facades\URL;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class QuotationCreated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $attach;

    protected $quotation;

    public function __construct(Quotation $quotation, $attach = true)
    {
        $this->quotation = $quotation;
        $this->attach    = $attach;
    }

    public function toMail($notifiable)
    {
        $url = URL::signedRoute('order', ['act' => 'quotation', 'hash' => $this->quotation->hash]);

        $mail = (new MailMessage())
            ->greeting(__('mps::mail.hello', ['name' => $this->quotation->customer->name]))
            ->subject(__('mps::mail.quotation_created_subject'))
            ->line(__('mps::mail.quotation_opening_line', ['reference' => $this->quotation->reference]))
            ->action(__('mps::mail.quotation_button_text'), $url)
            ->line(__('mps::mail.quotation_closing_line'));

        if ($this->attach) {
            try {
                $settings = json_decode(mps_config(), true);
                unset($settings['svg_string'], $settings['json_string']);
                // $this->quotation->attributes = extra_attributes('quotation');
                $this->quotation->loadMissing(['location', 'customer', 'items' => fn ($q) => $q->withAll()]);
                $pdf = app()->make('dompdf.wrapper');
                $pdf->loadView('mps::pdf.quotation', ['settings' => $settings, 'quotation' => $this->quotation]);
                $mail->attachData($pdf->output(), $this->quotation->id . '.pdf', ['mime' => 'application/pdf']);
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
