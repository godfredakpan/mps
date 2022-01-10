<?php

namespace Modules\MPS\Http\Controllers\Reports;

use Illuminate\Http\Request;
use Modules\MPS\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Modules\MPS\Http\Controllers\Controller;

class SaleReportController extends Controller
{
    public function index(Request $request)
    {
        $end_date   = $request->end_date ?: now()->endOfDay();
        $start_date = $request->start_date ?: now()->subMonths(3)->startOfDay();

        $sales = $this->prepareRequest($request)->select(
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
        return response()->json(['sales' => $sales, 'end_date' => $end_date, 'start_date' => $start_date]);
    }

    public function table(Request $request)
    {
        $sales = $this->prepareRequest($request);
        return response()->json($sales->table([]));
    }

    private function prepareRequest(Request $request)
    {
        $sales      = Sale::query();
        $end_date   = $request->end_date ?: now()->endOfDay();
        $start_date = $request->start_date ?: now()->subMonths(3)->startOfDay();
        $sales->whereBetween('date', [$start_date, $end_date]);

        if ($request->customer_id) {
            $sales->where('customer_id', $request->input('customer_id'));
        }
        if ($request->user_id) {
            $sales->where('user_id', $request->input('user_id'));
        }
        if ($request->custom_fields) {
            $sales->where('extra_attributes', 'like', "%{$request->input('custom_fields')}%");
        }
        if ($request->item_id) {
            $sales->whereHas('items', fn (Builder $q) => $q->where('item_id', $request->input('item_id')));
        }
        // TODO
        // if ($request->serial) {
        //     $sales->whereHas('items.serials', fn (Builder $q) => $q->where('number', $request->input('serial')));
        // }
        if ($request->draft != null && ($request->draft == 0 || $request->draft == 1)) {
            if ($request->draft == 1) {
                $sales->active();
            } else {
                $sales->draft();
            }
        }
        if ($request->paid != null && ($request->paid == 0 || $request->paid == 1)) {
            if ($request->paid == 1) {
                $sales->paid();
            } else {
                $sales->unpaid();
            }
        }
        if ($request->pos != null && ($request->pos == 0 || $request->pos == 1)) {
            if ($request->pos == 1) {
                $sales->pos();
            } else {
                $sales->nonPos();
            }
        }
        if ($request->void != null && ($request->void == 0 || $request->void == 1)) {
            if ($request->void == 1) {
                $sales->void();
            } else {
                $sales->valid();
            }
        }

        return $sales;
    }
}
