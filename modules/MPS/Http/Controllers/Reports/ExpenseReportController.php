<?php

namespace Modules\MPS\Http\Controllers\Reports;

use Illuminate\Http\Request;
use Modules\MPS\Models\Expense;
use Illuminate\Database\Eloquent\Builder;
use Modules\MPS\Http\Controllers\Controller;

class ExpenseReportController extends Controller
{
    public function index(Request $request)
    {
        $end_date   = $request->end_date ?: now()->endOfDay();
        $start_date = $request->start_date ?: now()->subMonths(3)->startOfDay();

        $expenses = $this->prepareRequest($request)
            ->selectRaw('COUNT(id) as count')
            ->selectRaw('SUM(amount) as amount')
            ->selectRaw('count(case when approved = 1 then 1 end) as approved')
            ->selectRaw('count(case when approved != 1 then 1 end) as unconfirmed')
            ->selectRaw('SUM(case when approved = 1 then amount end) as approved_amount')
            ->selectRaw('SUM(case when approved != 1 then amount end) as unconfirmed_amount')
            ->first();
        return response()->json(['expenses' => $expenses, 'end_date' => $end_date, 'start_date' => $start_date]);
    }

    public function table(Request $request)
    {
        $expenses = $this->prepareRequest($request)->with(['account:id,name', 'categories:id,name', 'user:id,name', 'approvedBy:id,name']);
        return response()->json($expenses->table([]));
    }

    private function prepareRequest(Request $request)
    {
        $expenses   = Expense::query();
        $end_date   = $request->end_date ?: now()->endOfDay();
        $start_date = $request->start_date ?: now()->subMonths(3)->startOfDay();
        $expenses->whereBetween('date', [$start_date, $end_date]);

        if ($request->title) {
            $expenses->where('title', 'like', "%{$request->input('title')}%");
        }
        if ($request->details) {
            $expenses->where('details', 'like', "%{$request->input('details')}%");
        }
        if ($request->account_id) {
            $expenses->where('account_id', $request->input('account_id'));
        }
        if ($request->category_id) {
            $expenses->whereHas('categories', fn (Builder $q) => $q->where('category_id', $request->input('category_id')));
        }
        if ($request->user_id) {
            $expenses->where('user_id', $request->input('user_id'));
        }
        if ($request->custom_fields) {
            $expenses->where('extra_attributes', 'like', "%{$request->input('custom_fields')}%");
        }
        if ($request->approved != null && ($request->approved == 0 || $request->approved == 1)) {
            if ($request->void == 1) {
                $expenses->approved();
            } else {
                $expenses->notApproved();
            }
        }

        return $expenses;
    }
}
