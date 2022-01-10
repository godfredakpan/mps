<?php

namespace Modules\MPS\Observers;

use Modules\MPS\Models\Expense;

class ExpenseObserver
{
    public function created(Expense $expense)
    {
        $ajt = $expense->account->journal->debitDollars(
            $expense->amount,
            'expense_created'
        )->referencesObject($expense);
        $expense->disableLogging();
        $expense->update(['transaction_id' => $ajt->id]);
    }

    public function deleting(Expense $expense)
    {
        $expense->account->journal->creditDollars(
            $expense->getOriginal('amount'),
            'expense_deleted'
        )->referencesObject($expense);
    }

    public function updating(Expense $expense)
    {
        if (!$expense->wasRecentlyCreated && $expense->isDirty('amount')) {
            // if (!$expense->wasRecentlyCreated && $expense->getOriginal('amount') != $expense->amount) {
            $expense->account->journal->creditDollars(
                $expense->getOriginal('amount'),
                'expense_updating'
            )->referencesObject($expense);

            $expense->account->journal->debitDollars(
                $expense->amount,
                'expense_updated'
            )->referencesObject($expense);
        }
    }
}
