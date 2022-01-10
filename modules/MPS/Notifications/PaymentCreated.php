<?php

namespace Modules\MPS\Notifications;

use Exception;
use Modules\MPS\Actions\Pdf;
use Illuminate\Bus\Queueable;
use Modules\MPS\Models\Payment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Nwidart\Modules\Facades\Module;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PaymentCreated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $attach;

    protected $payment;

    public function __construct(Payment $payment, $attach = true)
    {
        $this->payment = $payment;
        $this->attach  = $attach;
    }

    public function toMail($notifiable)
    {
        $module = Module::find('Shop');
        if ($module && $module->isEnabled()) {
            $url = URL::signedRoute('shop.payment.view', ['payment' => $this->payment->id, 'type' => 'pay']);
        } else {
            $url = URL::signedRoute('order', ['act' => 'payment', 'hash' => $this->payment->hash]);
        }

        $mail = (new MailMessage())
            ->greeting(__('mps::mail.hello', ['name' => $this->payment->payable->name]))
            ->subject(__('mps::mail.payment_created_subject'))
            ->line(__('mps::mail.payment_created_opening_line', ['reference' => $this->payment->reference]))
            ->action(__('mps::mail.payment_created_button_text'), $url)
            ->line(__('mps::mail.payment_created_closing_line'));

        if ($this->attach) {
            $settings = json_decode(mps_config(), true);
            $this->payment->loadMissing(['account:id,name', 'location', 'payable']);
            unset($settings['svg_string'], $settings['json_string']);
            $pdf = app()->make('dompdf.wrapper');
            $pdf->loadView('mps::pdf.payment', ['settings' => $settings, 'payment' => $this->payment]);
            $mail->attachData($pdf->output(), $this->payment->id . '.pdf', ['mime' => 'application/pdf']);
        }

        return $mail;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    private function pdf()
    {
        try {
            $route = module('route');
            $file  = storage_path('app/pdfs/' . $this->payment->id . '.pdf');
            Pdf::save(url($route . '#/views/payment/' . $this->payment->hash), $file);
        } catch (Exception $e) {
            $file = false;
            Log::info('Unable to generate pdf file for ' . $this->payment->id);
        }
        return $file;
    }
}
