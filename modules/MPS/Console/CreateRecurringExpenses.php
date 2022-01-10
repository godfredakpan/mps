<?php

namespace Modules\MPS\Console;

use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use Illuminate\Console\Command;
use Modules\MPS\Models\Expense;
use Illuminate\Support\Facades\DB;

class CreateRecurringExpenses extends Command
{
    protected $description = 'Create recurring expenses';

    protected $signature = 'recurring:expenses';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $recurring_expenses = Expense::with('categories')->recurring()->approved()->get()->filter(function ($recurring_expense) {
            $date = $recurring_expense->last_created_at ? $recurring_expense->last_created_at : $recurring_expense->start_date;
            if ($recurring_expense->create_before) {
                $date = $recurring_expense->create_before == 1 ? $date->subDay() : $date->subDays($recurring_expense->create_before);
            }
            return $this->{$recurring_expense->repeat}($date);
        });

        $recurring_expenses->each(function ($recurring_expense) {
            $number = DB::table($recurring_expense->getTable())->max('number');
            $number = $number ? ((int) $number) + 1 : 1;
            $expense = $recurring_expense->replicate()->fill([
                'repeat'          => null,
                'approved'        => null,
                'recurring'       => null,
                'start_date'      => null,
                'last_created_at' => null,
                'number'          => $number,
                'expense_id'      => $recurring_expense->id,
                'id'              => Uuid::uuid4()->toString(),
            ]);
            $expense->save();
            $expense->categories()->sync($recurring_expense->categories->first()->id);
            $recurring_expense->last_created_at = date('Y-m-d');
            $recurring_expense->saveQuietly();
        });

        $expenses_text = __choice('expense', [], $recurring_expenses->count());
        activity()->withProperties($recurring_expenses)
            ->log($recurring_expenses->count() . ' ' . $expenses_text . ' have been created with recurring:expenses command.');
        $this->info(sprintf('%d ' . $expenses_text . ' have been created with recurring:expenses command.', $recurring_expenses->count()));
    }

    private function biennially($date)
    {
        return $date->startOfDay()->addYears(2)->lte(Carbon::now()->startOfDay());
    }

    private function daily($date)
    {
        return $date->startOfDay()->addDay()->lte(Carbon::now()->startOfDay());
    }

    private function monthly($date)
    {
        return $date->startOfDay()->addMonth()->lte(Carbon::now()->startOfDay());
    }

    private function quarterly($date)
    {
        return $date->startOfDay()->addQuarter()->lte(Carbon::now()->startOfDay());
    }

    private function semiannual($date)
    {
        return $date->startOfDay()->addMonths(6)->lte(Carbon::now()->startOfDay());
    }

    private function weekly($date)
    {
        return $date->startOfDay()->addWeek()->lte(Carbon::now()->startOfDay());
    }

    private function yearly($date)
    {
        return $date->startOfDay()->addYear()->lte(Carbon::now()->startOfDay());
    }
}
