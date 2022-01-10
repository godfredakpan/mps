<?php

namespace Modules\MPS\Observers;

use Money\Money;
use Carbon\Carbon;
use Money\Currency;
use Illuminate\Support\Facades\Log;
use Modules\MPS\Models\JournalTransaction;

class JournalTransactionObserver
{
    public function saved(JournalTransaction $transaction)
    {
        if ($transaction->subject_id && $transaction->type != 'gateway_fees' && $transaction->type != 'payment_updating' && $transaction->type != 'payment_deleted') {
            $account = $transaction->journal->morphed;
            if ($account->fees) {
                if ($account->apply_to == 'in' && $transaction->credit > 0) {
                    $this->deductFee($transaction, $account);
                // Log::info('Journal Transaction is being applied to credit.');
                } elseif ($account->apply_to == 'out' && $transaction->debit > 0) {
                    $this->deductFee($transaction, $account);
                // Log::info('Journal Transaction is being applied to debit.');
                } elseif ($account->apply_to == 'both') {
                    $this->deductFee($transaction, $account);
                    // Log::info('Journal Transaction is being applied to transaction (credit & debit).');
                }
                // Log::info('Journal Transaction has been created.', $transaction->toArray());
            }
        }
    }

    private function deductFee(JournalTransaction $transaction, $account)
    {
        $fees   = $account->fixed ? $account->fixed * 100 : 0;
        $amount = ($transaction->credit ?: $transaction->debit ?: 0) - $fees;
        $fees += (int) ($account->percentage != 0 ? ($amount * $account->percentage / 100) : 0);
        // $account->journal->credit($fees, 'gateway_fees', 'Gateway Fees for ' . $transaction->subject_type . 'id: ' . $transaction->subject_id)->referencesObject($transaction);
        // $credit = new Money($fees, new Currency('USD')); // TODO

        $fee_transaction                         = new JournalTransaction;
        $fee_transaction->credit                 = null;
        $fee_transaction->currency               = 'USD'; // TODO
        $fee_transaction->debit                  = $fees;
        $fee_transaction->type                   = 'gateway_fees';
        $fee_transaction->memo                   = 'Gateway Fees';
        $fee_transaction->post_date              = Carbon::now();
        $fee_transaction->subject_id             = $transaction->subject_id;
        $fee_transaction->subject_type           = $transaction->subject_type;
        $fee_transaction->journal_transaction_id = $transaction->id;
        $account->journal->transactions()->save($fee_transaction);
    }
}
