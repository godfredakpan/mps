<?php

namespace Modules\MPS\Http\Controllers\Reports;

use Illuminate\Http\Request;
use Modules\MPS\Models\Purchase;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Modules\MPS\Http\Controllers\Controller;

class PurchaseReportController extends Controller
{
    public function index(Request $request)
    {
        $end_date   = $request->end_date ?: now()->endOfDay();
        $start_date = $request->start_date ?: now()->subMonths(3)->startOfDay();

        $purchases = $this->prepareRequest($request)->select(
            DB::raw('SUM(total) as total'),
            DB::raw('SUM(shipping) as shipping'),
            DB::raw('SUM(grand_total) as grand_total'),
            DB::raw('SUM(discount_amount) as discount_amount'),
            DB::raw('SUM(item_tax_amount) as item_tax_amount'),
            DB::raw('SUM(order_tax_amount) as order_tax_amount'),
            DB::raw('SUM(total_tax_amount) as total_tax_amount'),
            DB::raw('SUM(recoverable_tax_amount) as recoverable_tax_amount'),
            DB::raw('SUM(recoverable_tax_calculated_on) as recoverable_tax_calculated_on'),
        )->active()->valid()->first();
        return response()->json(['purchases' => $purchases, 'end_date' => $end_date, 'start_date' => $start_date]);
    }

    public function table(Request $request)
    {
        $purchases = $this->prepareRequest($request);
        return response()->json($purchases->table([]));
    }

    private function prepareRequest(Request $request)
    {
        $purchases  = Purchase::query();
        $end_date   = $request->end_date ?: now()->endOfDay();
        $start_date = $request->start_date ?: now()->subMonths(3)->startOfDay();
        $purchases->whereBetween('date', [$start_date, $end_date]);

        if ($request->supplier_id) {
            $purchases->where('supplier_id', $request->input('supplier_id'));
        }
        if ($request->user_id) {
            $purchases->where('user_id', $request->input('user_id'));
        }
        if ($request->custom_fields) {
            $purchases->where('extra_attributes', 'like', "%{$request->input('custom_fields')}%");
        }
        if ($request->item_id) {
            $purchases->whereHas('items', fn (Builder $q) => $q->where('item_id', $request->input('item_id')));
        }
        // TODO
        // if ($request->serial) {
        //     $purchases->whereHas('items.serials', fn (Builder $q) => $q->where('number', $request->input('serial')));
        // }
        if ($request->draft != null && ($request->draft == 0 || $request->draft == 1)) {
            if ($request->draft == 1) {
                $purchases->active();
            } else {
                $purchases->draft();
            }
        }
        if ($request->paid != null && ($request->paid == 0 || $request->paid == 1)) {
            if ($request->paid == 1) {
                $purchases->paid();
            } else {
                $purchases->unpaid();
            }
        }
        if ($request->void != null && ($request->void == 0 || $request->void == 1)) {
            if ($request->void == 1) {
                $purchases->void();
            } else {
                $purchases->valid();
            }
        }

        return $purchases;
    }
}
