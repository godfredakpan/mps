<?php

namespace Modules\MPS\Services;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Modules\MPS\Models\Sale;
use Modules\MPS\Models\Income;
use Modules\MPS\Models\Expense;
use Modules\MPS\Models\Purchase;
use Illuminate\Support\Facades\DB;

class ChartService
{
    public $month;

    public $year;

    public function __construct($month = null, $year = null)
    {
        $now         = now();
        $this->month = $month ?: $now->format('n');
        $this->year  = $year ?: $now->format('Y');
    }

    public function month()
    {
        $this->month = $this->month > 9 ? $this->month : '0' . $this->month;
        $end_date    = Carbon::parse($this->year . '-' . $this->month . '-01')->endOfMonth();
        $start_date  = Carbon::parse($this->year . '-' . $this->month . '-01')->startOfMonth();

        if (env('DB_CONNECTION') == 'sqlite') {
            $monthlyIncomes = Income::selectRaw(DB::Raw('date, SUM(amount) as total'))
                ->whereBetween('date', [$start_date, $end_date])->groupBy('date')->orderBy('date')->get();

            $monthlyExpenses = Expense::selectRaw(DB::Raw('date, SUM(amount) as total'))
                ->whereBetween('date', [$start_date, $end_date])->groupBy('date')->orderBy('date')->get();

            $monthlySales = Sale::selectRaw(DB::Raw('date, SUM(grand_total) as total'))
                ->whereBetween('date', [$start_date, $end_date])->groupBy('date')->orderBy('date')->get();

            $monthlyPurchases = Purchase::selectRaw(DB::Raw('date, SUM(grand_total) as total'))
                ->whereBetween('date', [$start_date, $end_date])->groupBy('date')->orderBy('date')->get();
        } else {
            $monthlyIncomes = Income::selectRaw(DB::Raw('date, SUM(amount) as total'))
                ->whereBetween('date', [$start_date, $end_date])->groupBy('date')->orderBy('date')->get();

            $monthlyExpenses = Expense::selectRaw(DB::Raw('date, SUM(amount) as total'))
                ->whereBetween('date', [$start_date, $end_date])->groupBy('date')->orderBy('date')->get();

            $monthlySales = Sale::selectRaw(DB::Raw('date, SUM(grand_total) as total'))
                ->whereBetween('date', [$start_date, $end_date])->groupBy('date')->orderBy('date')->get();

            $monthlyPurchases = Purchase::selectRaw(DB::Raw('date, SUM(grand_total) as total'))
                ->whereBetween('date', [$start_date, $end_date])->groupBy('date')->orderBy('date')->get();
        }

        return [
            'sale'     => $this->prepareMonthArray($monthlySales, $start_date),
            'purchase' => $this->prepareMonthArray($monthlyPurchases, $start_date),
            'income'   => $this->prepareMonthArray($monthlyIncomes, $start_date),
            'expense'  => $this->prepareMonthArray($monthlyExpenses, $start_date),
        ];
    }

    public function week()
    {
        $end_date   = now()->endOfDay()->format('Y-m-d');
        $start_date = now()->subDays(6)->startOfDay()->format('Y-m-d');

        if (env('DB_CONNECTION') == 'sqlite') {
            $dailyIncomes = Income::selectRaw(DB::Raw('date, SUM(amount) as total'))
                ->whereBetween('date', [$start_date, $end_date])->groupBy('date')->orderBy('date')->get();
            $dailyExpenses = Expense::selectRaw(DB::Raw('date, SUM(amount) as total'))
                ->whereBetween('date', [$start_date, $end_date])->groupBy('date')->orderBy('date')->get();
            $dailySales = Sale::selectRaw(DB::Raw('date, SUM(grand_total) as total'))
                ->whereBetween('date', [$start_date, $end_date])->groupBy('date')->orderBy('date')->get();
            $dailyPurchases = Purchase::selectRaw(DB::Raw('date, SUM(grand_total) as total'))
                ->whereBetween('date', [$start_date, $end_date])->groupBy('date')->orderBy('date')->get();
        } else {
            $dailyIncomes = Income::selectRaw(DB::Raw('date, SUM(amount) as total'))
                ->whereBetween('date', [$start_date, $end_date])->groupBy('date')->orderBy('date')->get();
            $dailyExpenses = Expense::selectRaw(DB::Raw('date, SUM(amount) as total'))
                ->whereBetween('date', [$start_date, $end_date])->groupBy('date')->orderBy('date')->get();
            $dailySales = Sale::selectRaw(DB::Raw('date, SUM(grand_total) as total'))
                ->whereBetween('date', [$start_date, $end_date])->groupBy('date')->orderBy('date')->get();
            $dailyPurchases = Purchase::selectRaw(DB::Raw('date, SUM(grand_total) as total'))
                ->whereBetween('date', [$start_date, $end_date])->groupBy('date')->orderBy('date')->get();
        }

        return [
            'sale'     => $this->prepareWeekArray($dailySales),
            'purchase' => $this->prepareWeekArray($dailyPurchases),
            'income'   => $this->prepareWeekArray($dailyIncomes),
            'expense'  => $this->prepareWeekArray($dailyExpenses),
        ];
    }

    public function year()
    {
        $end_date   = Carbon::parse($this->year . '-01-01')->endOfYear();
        $start_date = Carbon::parse($this->year . '-01-01')->startOfYear();

        if (env('DB_CONNECTION') == 'sqlite') {
            $monthlyIncomes = Income::selectRaw(
                DB::Raw("strftime('%m', date) as month, strftime('%Y', date) as year, SUM(amount) as total")
            )
                ->whereBetween('date', [$start_date, $end_date])
                ->groupBy(DB::raw("strftime('%Y', date), strftime('%m', date)"))
                ->orderBy(DB::raw("strftime('%Y-%m', date)"))->get();

            $monthlyExpenses = Expense::selectRaw(
                DB::Raw("strftime('%m', date) as month, strftime('%Y', date) as year, SUM(amount) as total")
            )
                ->whereBetween('date', [$start_date, $end_date])
                ->groupBy(DB::raw("strftime('%Y', date), strftime('%m', date)"))
                ->orderBy(DB::raw("strftime('%Y-%m', date)"))->get();

            $monthlySales = Sale::selectRaw(
                DB::Raw("strftime('%m', date) as month, strftime('%Y', date) as year, SUM(grand_total) as total")
            )
                ->whereBetween('date', [$start_date, $end_date])
                ->groupBy(DB::raw("strftime('%Y', date), strftime('%m', date)"))
                ->orderBy(DB::raw("strftime('%Y-%m', date)"))->get();

            $monthlyPurchases = Purchase::selectRaw(
                DB::Raw("strftime('%m', date) as month, strftime('%Y', date) as year, SUM(grand_total) as total")
            )
                ->whereBetween('date', [$start_date, $end_date])
                ->groupBy(DB::raw("strftime('%Y', date), strftime('%m', date)"))
                ->orderBy(DB::raw("strftime('%Y-%m', date)"))->get();
        } else {
            $monthlyIncomes = Income::selectRaw(
                DB::Raw('MONTH(date) as month, YEAR(date) as year, SUM(amount) as total')
            )
                ->whereBetween('date', [$start_date, $end_date])
                ->groupBy(DB::raw('YEAR(date), MONTH(date)'))
                ->orderBy('year')->orderBy('month')->get();

            $monthlyExpenses = Expense::selectRaw(
                DB::Raw('MONTH(date) as month, YEAR(date) as year, SUM(amount) as total')
            )
                ->whereBetween('date', [$start_date, $end_date])
                ->groupBy(DB::raw('YEAR(date), MONTH(date)'))
                ->orderBy('year')->orderBy('month')->get();

            $monthlySales = Sale::selectRaw(
                DB::Raw('MONTH(date) as month, YEAR(date) as year, SUM(grand_total) as total')
            )
                ->whereBetween('date', [$start_date, $end_date])
                ->groupBy(DB::raw('YEAR(date), MONTH(date)'))
                ->orderBy('year')->orderBy('month')->get();

            $monthlyPurchases = Purchase::selectRaw(
                DB::Raw('MONTH(date) as month, YEAR(date) as year, SUM(grand_total) as total')
            )
                ->whereBetween('date', [$start_date, $end_date])
                ->groupBy(DB::raw('YEAR(date), MONTH(date)'))
                ->orderBy('year')->orderBy('month')->get();
        }

        return [
            'sale'     => $this->prepareYearArray($monthlySales, $start_date),
            'purchase' => $this->prepareYearArray($monthlyPurchases, $start_date),
            'income'   => $this->prepareYearArray($monthlyIncomes, $start_date),
            'expense'  => $this->prepareYearArray($monthlyExpenses, $start_date),
        ];
    }

    private function prepareMonthArray($array, $date)
    {
        $days = $date->daysInMonth;
        $data = $array->mapWithKeys(function ($item) {
            return [$item->date->toDateString() => $item->total];
        })->toArray();
        for ($i = 1; $i <= $days; $i++) {
            $key        = Carbon::parse($date->format('Y-m-') . ($i < 10 ? '0' . ((int)  $i) : $i))->format('Y-m-d');
            $data[$key] = Arr::has($data, $key) ? (0 + $data[$key]) : 0;
        }
        return $data;
    }

    private function prepareWeekArray($array)
    {
        $data = $array->mapWithKeys(function ($item) {
            return [$item->date->toDateString() => $item->total];
        })->toArray();
        for ($i = 6; $i >= 0; $i--) {
            if ($i == 0) {
                $date = now();
            } elseif ($i == 1) {
                $date = now()->subDay();
            } else {
                $date = now()->subDays($i);
            }
            $key        = $date->format('Y-m-d');
            $data[$key] = Arr::has($data, $key) ? (0 + $data[$key]) : 0;
        }
        return $data;
    }

    private function prepareYearArray($array, $date)
    {
        $data = $array->mapWithKeys(function ($item) {
            return [$item['year'] . ($item['month'] < 10 ? '-0' : '-') . ((int) $item['month']) => $item['total']];
        })->toArray();
        for ($i = 1; $i <= 12; $i++) {
            $key        = Carbon::parse($date->format('Y-') . ($i < 10 ? '0' . $i : $i))->format('Y-m');
            $data[$key] = Arr::has($data, $key) ? (0 + $data[$key]) : 0;
        }
        return $data;
    }
}
