<?php

namespace Modules\MPS\Http\Controllers\Reports;

use Illuminate\Http\Request;
use Modules\MPS\Models\StockAdjustment;
use Illuminate\Database\Eloquent\Builder;
use Modules\MPS\Http\Controllers\Controller;

class StockAdjustmentReportController extends Controller
{
    public function index(Request $request)
    {
        $end_date   = $request->end_date ?: now()->endOfDay();
        $start_date = $request->start_date ?: now()->subMonths(3)->startOfDay();

        $stock_adjustments = $this->prepareRequest($request)
            ->selectRaw('COUNT(id) as count')
            ->selectRaw('SUM(total) as total')
            ->selectRaw('SUM(grand_total) as grand_total')
            ->selectRaw('SUM(total_tax_amount) as total_tax_amount')
            ->selectRaw('count(case when type = "addition" then 1 end) as additions')
            ->selectRaw('count(case when type = "damage" then 1 end) as damages')
            ->selectRaw('count(case when type = "subtraction" then 1 end) as subtractions')
            ->selectRaw('SUM(case when type = "addition" then grand_total end) as additions_total')
            ->selectRaw('SUM(case when type = "damage" then grand_total end) as damages_total')
            ->selectRaw('SUM(case when type = "subtraction" then grand_total end) as subtractions_total')
            ->first();
        return response()->json(['stock_adjustments' => $stock_adjustments, 'end_date' => $end_date, 'start_date' => $start_date]);
    }

    public function table(Request $request)
    {
        $stock_adjustments = $this->prepareRequest($request)->with(['user:id,name']);
        return response()->json($stock_adjustments->table([]));
    }

    private function prepareRequest(Request $request)
    {
        $stock_adjustments = StockAdjustment::query();
        $end_date          = $request->end_date ?: now()->endOfDay();
        $start_date        = $request->start_date ?: now()->subMonths(3)->startOfDay();
        $stock_adjustments->whereBetween('date', [$start_date, $end_date]);

        if ($request->type) {
            $stock_adjustments->where('type', $request->input('type'));
        }
        if ($request->details) {
            $stock_adjustments->where('details', 'like', "%{$request->input('details')}%");
        }
        if ($request->account_id) {
            $stock_adjustments->where('account_id', $request->input('account_id'));
        }
        if ($request->item_id) {
            $stock_adjustments->whereHas('items', fn (Builder $q) => $q->where('item_id', $request->input('item_id')));
        }
        // TODO
        // if ($request->serial) {
        //     $stock_adjustments->whereHas('items.serials', fn (Builder $q) => $q->where('number', $request->input('serial')));
        // }
        if ($request->user_id) {
            $stock_adjustments->where('user_id', $request->input('user_id'));
        }
        if ($request->custom_fields) {
            $stock_adjustments->where('extra_attributes', 'like', "%{$request->input('custom_fields')}%");
        }
        if ($request->draft != null && ($request->draft == 0 || $request->draft == 1)) {
            if ($request->draft == 1) {
                $stock_adjustments->active();
            } else {
                $stock_adjustments->draft();
            }
        }

        return $stock_adjustments;
    }
}
