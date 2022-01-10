<?php

namespace Modules\MPS\Observers;

use Modules\MPS\Models\Account;
use Modules\MPS\Models\Payment;
use Modules\MPS\Models\JournalTransaction;

class PaymentObserver
{
    public function created(Payment $payment)
    {
        if ($payment->received) {
            $payer      = $payment->payable;
            $account    = Account::find($payment->account_id);
            $isCustomer = ($payer instanceof \Modules\MPS\Models\Customer);

            $payer_record = $payer->journal
                ->debitDollars($payment->amount, 'payment_created')
                ->referencesObject($payment);

            if ($isCustomer) {
                $account_record = $account->journal
                    ->creditDollars($payment->amount, 'payment_received')
                    ->referencesObject($payment);
            } else {
                $account_record = $account->journal
                    ->debitDollars($payment->amount, 'payment_sent')
                    ->referencesObject($payment);
            }

            $payment->fill([
                'payer_transaction_id'   => $payer_record->id,
                'account_transaction_id' => $account_record->id,
            ])->saveQuietly();
            event(new \Modules\MPS\Events\PaymentEvent($payment));
            if (safe_email($payer->email)) {
                if (!$isCustomer || !default_customer($payer->id)) {
                    $payer->notify(new \Modules\MPS\Notifications\PaymentReceived($payment));
                }
            }
        } else {
            if (safe_email($payment->payable->email)) {
                if (!($payment->payable instanceof \Modules\MPS\Models\Customer) || !default_customer($payment->payable->id)) {
                    $payment->payable->notify(new \Modules\MPS\Notifications\PaymentCreated($payment));
                }
            } elseif (default_customer($payment->payable->id) && $payment->sale && $payment->sale->shop && $payment->sale->address && safe_email($payment->sale->address->email)) {
                $payment->sale->address->notify(new \Modules\MPS\Notifications\PaymentCreated($payment));
            }
        }
    }

    public function deleted(Payment $payment)
    {
        if ($payment->received) {
            $payer   = $payment->payable;
            $account = Account::find($payment->account_id);

            $payer_record = $payer->journal
                ->creditDollars($payment->amount, 'payment_deleted')
                ->referencesObject($payment);

            if ($payer instanceof \Modules\MPS\Models\Customer) {
                $account_record = $account->journal
                    ->debitDollars($payment->amount, 'payment_deleted')
                    ->referencesObject($payment);
                if ($account->fees && $account->apply_to != 'out') {
                    $this->reverseFee($account_record);
                }
            } else {
                $account_record = $account->journal
                    ->creditDollars($payment->amount, 'payment_deleted')
                    ->referencesObject($payment);
                if ($account->fees && $account->apply_to != 'in') {
                    $this->reverseFee($account_record);
                }
            }
        }
    }

    public function updating(Payment $payment)
    {
        // if (!$payment->wasRecentlyCreated) {
        // if ($payment->getOriginal('updated_at') && $payment->getOriginal('updated_at') != $payment->updated_at) {
        if ($payment->received) {
            if (!$payment->getOriginal('received')) {
                $payer      = $payment->payable;
                $account    = Account::find($payment->account_id);
                $isCustomer = ($payer instanceof \Modules\MPS\Models\Customer);

                $payer_record = $payer->journal
                    ->debitDollars($payment->amount, 'payment_created')
                    ->referencesObject($payment);

                if ($isCustomer) {
                    $account_record = $account->journal
                        ->creditDollars($payment->amount, 'payment_received')
                        ->referencesObject($payment);
                } else {
                    $account_record = $account->journal
                        ->debitDollars($payment->amount, 'payment_sent')
                        ->referencesObject($payment);
                }

                $payment->disableLogging();
                $temp = $payment->getEventDispatcher();
                $payment->unsetEventDispatcher();
                $payment->update([
                    'payer_transaction_id'   => $payer_record->id,
                    'account_transaction_id' => $account_record->id,
                ]);
                $payment->setEventDispatcher($temp);
                event(new \Modules\MPS\Events\PaymentEvent($payment));
                if (safe_email($payer->email)) {
                    if (!$isCustomer || !default_customer($payer->id)) {
                        $payer->notify(new \Modules\MPS\Notifications\PaymentReceived($payment));
                    } elseif (default_customer($payer->id) && $payment->sale && $payment->sale->shop && $payment->sale->address && safe_email($payment->sale->address->email)) {
                        $payment->sale->address->notify(new \Modules\MPS\Notifications\PaymentReceived($payment->sale));
                    }
                }
            } elseif ($payment->isDirty('amount') || $payment->isDirty('account_id')) {
                // } elseif ($payment->amount != $payment->getOriginal('amount')) {
                $payer = $payment->getOriginal('payable_type')::find($payment->getOriginal('payable_id'));
                log_activity('Updating payment', $payment, $payer);
                $account = Account::find($payment->getOriginal('account_id'));

                $payer_record = $payer->journal
                        ->creditDollars($payment->getOriginal('amount'), 'payment_updating')
                        ->referencesObject($payment);

                if ($payer instanceof \Modules\MPS\Models\Customer) {
                    $account_record = $account->journal
                        ->debitDollars($payment->getOriginal('amount'), 'payment_updating')
                        ->referencesObject($payment);
                    if ($account->fees && $account->apply_to != 'out') {
                        $this->reverseFee($account_record);
                    }
                } else {
                    $account_record = $account->journal
                        ->creditDollars($payment->getOriginal('amount'), 'payment_updating')
                        ->referencesObject($payment);
                    if ($account->fees && $account->apply_to != 'in') {
                        $this->reverseFee($account_record);
                    }
                }

                $payer   = $payment->payable;
                $account = Account::find($payment->account_id);

                $payer_record = $payer->journal
                    ->debitDollars($payment->amount, 'payment_updated')
                    ->referencesObject($payment);

                if ($payer instanceof \Modules\MPS\Models\Customer) {
                    $account_record = $account->journal
                        ->creditDollars($payment->amount, 'payment_updated')
                        ->referencesObject($payment);
                } else {
                    $account_record = $account->journal
                        ->debitDollars($payment->amount, 'payment_updated')
                        ->referencesObject($payment);
                }
                event(new \Modules\MPS\Events\PaymentEvent($payment, true));
            }
        }
        // }
    }

    private function reverseFee(JournalTransaction $transaction)
    {
        $fee_debit_transaction = $transaction->journal->transactions()->without('subject')
            ->where('debit', '>', 0)->where('type', 'gateway_fees')
            ->where('subject_id', $transaction->subject_id)->where('subject_type', $transaction->subject_type)->latest()->first();
        // $fee_credit_transaction = $transaction->journal->transactions()
        //     ->where('credit', '>', 0)->where('type', 'gateway_fees')
        //     ->where('subject_id', $transaction->subject_id)->where('subject_type', $transaction->subject_type)->exists();

        if ($fee_debit_transaction) {
            $fee_reversal_transaction         = $fee_debit_transaction->replicate();
            $fee_reversal_transaction->credit = $fee_reversal_transaction->debit;
            $fee_reversal_transaction->debit  = 0;
            $fee_reversal_transaction->memo   = 'Gateway Fees Reversal';
            $fee_reversal_transaction->save();
        }
    }
}
