<?php

namespace Modules\MPS\Http\Controllers\Reports;

use Illuminate\Http\Request;
use Modules\MPS\Models\StockTransfer;
use Illuminate\Database\Eloquent\Builder;
use Modules\MPS\Http\Controllers\Controller;

class StockTransferReportController extends Controller
{
    public function index(Request $request)
    {
        $end_date   = $request->end_date ?: now()->endOfDay();
        $start_date = $request->start_date ?: now()->subMonths(3)->startOfDay();

        $stock_transfers = $this->prepareRequest($request)->selectRaw('COUNT(id) as count')->first();
        return response()->json(['stock_transfers' => $stock_transfers, 'end_date' => $end_date, 'start_date' => $start_date]);
    }

    public function table(Request $request)
    {
        $stock_transfers = $this->prepareRequest($request)->with(['user:id,name']);
        return response()->json($stock_transfers->table([]));
    }

    private function prepareRequest(Request $request)
    {
        $stock_transfers = StockTransfer::query();
        $end_date        = $request->end_date ?: now()->endOfDay();
        $start_date      = $request->start_date ?: now()->subMonths(3)->startOfDay();
        $stock_transfers->whereBetween('created_at', [$start_date, $end_date]);

        if ($request->status) {
            $stock_transfers->where('status', $request->input('status'));
        }
        if ($request->details) {
            $stock_transfers->where('details', 'like', "%{$request->input('details')}%");
        }
        if ($request->to) {
            $stock_transfers->where('to', $request->input('to_location_id'));
        }
        if ($request->from) {
            $stock_transfers->where('from', $request->input('from_location_id'));
        }
        if ($request->item_id) {
            $stock_transfers->whereHas('items', fn (Builder $q) => $q->where('item_id', $request->input('item_id')));
        }
        // TODO
        // if ($request->serial) {
        //     $stock_transfers->whereHas('items.serials', fn (Builder $q) => $q->where('number', $request->input('serial')));
        // }
        if ($request->user_id) {
            $stock_transfers->where('user_id', $request->input('user_id'));
        }
        if ($request->custom_fields) {
            $stock_transfers->where('extra_attributes', 'like', "%{$request->input('custom_fields')}%");
        }
        if ($request->draft != null && ($request->draft == 0 || $request->draft == 1)) {
            if ($request->draft == 1) {
                $stock_transfers->active();
            } else {
                $stock_transfers->draft();
            }
        }

        return $stock_transfers;
    }
}
