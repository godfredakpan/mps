<?php

namespace Modules\MPS\Console;

use Illuminate\Console\Command;

class SendPaymentReminders extends Command
{
    protected $description = 'Send reminder for due payments';

    protected $signature = 'payment:reminder';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $payments = \Modules\MPS\Models\Payment::due()->get();
        $payments->each(function ($payment) {
            $payment->payable->notify(new \Modules\MPS\Notifications\PaymentReminder($payment));
        });

        $payments_text = __('payment', [], $payments->count());
        activity()->withProperties($payments)
            ->log($payments->count() . ' ' . $payments_text . ' reminder have been sent with payment:reminder command.');
        $this->info(sprintf('%d ' . $payments_text . ' reminder have been sent with payment:reminder command.', $payments->count()));
    }
}
