<?php

namespace Modules\MPS\Notifications;

use Modules\MPS\Models\Sale;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\URL;
use Nwidart\Modules\Facades\Module;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SaleCreated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $attach;

    protected $sale;

    public function __construct(Sale $sale, $attach = true)
    {
        $this->sale   = $sale;
        $this->attach = $attach;
    }

    public function toMail($notifiable)
    {
        $module = Module::find('Shop');
        if ($module && $module->isEnabled()) {
            $url = URL::signedRoute('shop.order.view', ['order' => $this->sale->id]);
        } else {
            $url = URL::signedRoute('order', ['act' => 'sale', 'hash' => $this->sale->hash]);
        }

        $mail = (new MailMessage())
            ->greeting(__('mps::mail.hello', ['name' => $this->sale->customer->name]))
            ->subject(__('mps::mail.sale_created_subject'))
            ->line(__('mps::mail.sale_opening_line', ['reference' => $this->sale->reference]))
            ->action(__('mps::mail.sale_button_text'), $url)
            ->line(__('mps::mail.sale_closing_line'));

        if ($this->attach) {
            try {
                $settings = json_decode(mps_config(), true);
                unset($settings['svg_string'], $settings['json_string']);
                // $this->sale->attributes = extra_attributes('sale');
                $this->sale->loadMissing(['location', 'customer', 'payments', 'items' => fn ($q) => $q->withAll()]);
                $pdf = app()->make('dompdf.wrapper');
                $pdf->loadView('mps::pdf.sale', ['settings' => $settings, 'sale' => $this->sale]);
                $mail->attachData($pdf->output(), $this->sale->id . '.pdf', ['mime' => 'application/pdf']);
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
