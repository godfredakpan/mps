<?php

namespace Modules\MPS\Observers;

use Modules\MPS\Models\Salary;

class SalaryObserver
{
    public function created(Salary $salary)
    {
        if (!$salary->transaction_id && $salary->status == 'paid') {
            $transaction = $salary->account->journal
                ->debitDollars($salary->amount, 'salary_paid')
                ->referencesObject($salary);
            $salary->disableLogging();
            $temp = $salary->getEventDispatcher();
            $salary->unsetEventDispatcher();
            $salary->update(['transaction_id' => $transaction->id]);
            $salary->setEventDispatcher($temp);

            if ($salary->type == 'commission' && $salary->points) {
                $salary->user->meta()->updateORCreate(['meta_key' => 'points'], ['meta_value' => (usermeta($salary->user->id, 'points') ?: 0) - $salary->points]);
            }
        }
    }

    public function deleting(Salary $salary)
    {
        if ($salary->status == 'paid') {
            $salary->account->journal->creditDollars($salary->amount, 'salary_deleting')->referencesObject($salary);

            if ($salary->type == 'commission' && $salary->points) {
                $salary->user->meta()->updateORCreate(['meta_key' => 'points'], ['meta_value' => (usermeta($salary->user->id, 'points') ?: 0) + $salary->points]);
            }
        }
    }

    public function updating(Salary $salary)
    {
        if (!$salary->wasRecentlyCreated) {
            if ($salary->getOriginal('status') != 'paid' && $salary->status == 'paid') {
                $transaction = $salary->account->journal
                    ->debitDollars($salary->amount, 'salary_paid')
                    ->referencesObject($salary);
                $salary->disableLogging();
                $temp = $salary->getEventDispatcher();
                $salary->unsetEventDispatcher();
                $salary->update(['transaction_id' => $transaction->id]);
                $salary->setEventDispatcher($temp);

                if ($salary->type == 'commission' && $salary->points) {
                    $salary->user->meta()->updateORCreate(['meta_key' => 'points'], ['meta_value' => (usermeta($salary->user->id, 'points') ?: 0) - $salary->points]);
                }
            } elseif ($salary->getOriginal('status') == 'paid' && $salary->status == 'paid') {
                $opoints = $salary->getOriginal('points');
                if ($salary->isDirty('amount')) {
                    $salary->account->journal->creditDollars($salary->getOriginal('amount'), 'salary_updating')->referencesObject($salary);
                    $transaction = $salary->account->journal->debitDollars($salary->amount, 'salary_updated')->referencesObject($salary);
                    $salary->disableLogging();
                    $temp = $salary->getEventDispatcher();
                    $salary->unsetEventDispatcher();
                    $salary->update(['transaction_id' => $transaction->id]);
                    $salary->setEventDispatcher($temp);
                }

                if ($salary->getOriginal('type') == 'commission' && $opoints) {
                    $salary->user->meta()->updateORCreate(['meta_key' => 'points'], ['meta_value' => (usermeta($salary->user->id, 'points') ?: 0) + $opoints]);
                }
                if ($salary->type == 'commission' && $salary->points) {
                    $salary->user->meta()->updateORCreate(['meta_key' => 'points'], ['meta_value' => (usermeta($salary->user->id, 'points') ?: 0) - $salary->points]);
                }
            } elseif ($salary->getOriginal('status') == 'paid' && $salary->status != 'paid') {
                $salary->account->journal->creditDollars($salary->amount, 'salary_updated')->referencesObject($salary);
                $salary->disableLogging();
                $temp = $salary->getEventDispatcher();
                $salary->unsetEventDispatcher();
                $salary->update(['transaction_id' => null]);
                $salary->setEventDispatcher($temp);

                if ($salary->type == 'commission' && $salary->points) {
                    $salary->user->meta()->updateORCreate(['meta_key' => 'points'], ['meta_value' => (usermeta($salary->user->id, 'points') ?: 0) + $salary->points]);
                }
            }
        }
    }
}
