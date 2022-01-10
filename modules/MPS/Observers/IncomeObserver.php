<?php

namespace Modules\MPS\Observers;

use Modules\MPS\Models\Income;

class IncomeObserver
{
    public function created(Income $income)
    {
        $ajt = $income->account->journal->creditDollars(
            $income->amount,
            'income_created'
        )->referencesObject($income);
        $income->disableLogging();
        $income->update(['transaction_id' => $ajt->id]);
    }

    public function deleting(Income $income)
    {
        $income->account->journal->debitDollars(
            $income->getOriginal('amount'),
            'income_deleted'
        )->referencesObject($income);
    }

    public function updating(Income $income)
    {
        if (!$income->wasRecentlyCreated && $income->isDirty('amount')) {
            // if (!$income->wasRecentlyCreated && $income->getOriginal('amount') != $income->amount) {
            $income->account->journal->debitDollars(
                $income->getOriginal('amount'),
                'income_updating'
            )->referencesObject($income);

            $income->account->journal->creditDollars(
                $income->amount,
                'income_updated'
            )->referencesObject($income);
        }
    }
}
