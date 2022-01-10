<?php

namespace Modules\MPS\Http\Controllers\Reports;

use Illuminate\Http\Request;
use Modules\MPS\Models\Income;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Modules\MPS\Http\Controllers\Controller;

class IncomeReportController extends Controller
{
    public function index(Request $request)
    {
        $end_date   = $request->end_date ?: now()->endOfDay();
        $start_date = $request->start_date ?: now()->subMonths(3)->startOfDay();

        $incomes = $this->prepareRequest($request)->select(
            DB::raw('COUNT(id) as count'),
            DB::raw('SUM(amount) as amount')
        )->first();
        return response()->json(['incomes' => $incomes, 'end_date' => $end_date, 'start_date' => $start_date]);
    }

    public function table(Request $request)
    {
        $incomes = $this->prepareRequest($request)->with(['account:id,name', 'categories:id,name', 'user:id,name']);
        return response()->json($incomes->table([]));
    }

    private function prepareRequest(Request $request)
    {
        $incomes    = Income::query();
        $end_date   = $request->end_date ?: now()->endOfDay();
        $start_date = $request->start_date ?: now()->subMonths(3)->startOfDay();
        $incomes->whereBetween('date', [$start_date, $end_date]);

        if ($request->title) {
            $incomes->where('title', 'like', "%{$request->input('title')}%");
        }
        if ($request->details) {
            $incomes->where('details', 'like', "%{$request->input('details')}%");
        }
        if ($request->account_id) {
            $incomes->where('account_id', $request->input('account_id'));
        }
        if ($request->category_id) {
            $incomes->whereHas('categories', fn (Builder $q) => $q->where('category_id', $request->input('category_id')));
        }
        if ($request->user_id) {
            $incomes->where('user_id', $request->input('user_id'));
        }
        if ($request->custom_fields) {
            $incomes->where('extra_attributes', 'like', "%{$request->input('custom_fields')}%");
        }

        return $incomes;
    }
}
