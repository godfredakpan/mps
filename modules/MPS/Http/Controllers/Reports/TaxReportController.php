<?php

namespace Modules\MPS\Http\Controllers\Reports;

use Illuminate\Http\Request;
use Modules\MPS\Models\Sale;
use Modules\MPS\Models\Purchase;
use Illuminate\Support\Facades\DB;
use Modules\MPS\Http\Controllers\Controller;

class TaxReportController extends Controller
{
    public function index(Request $request)
    {
        $end_date   = $request->end_date ?: now()->endOfDay();
        $start_date = $request->start_date ?: now()->subMonths(3)->startOfDay();

        $sales = Sale::query()->without(['attachments', 'user', 'customer', 'deliveries'])
            ->whereBetween('date', [$start_date, $end_date])->select(
                DB::raw('SUM(grand_total) as grand_total'),
                DB::raw('SUM(total_tax_amount) as total_tax_amount'),
                DB::raw('SUM(recoverable_tax_amount) as recoverable_tax_amount'),
                DB::raw('SUM(recoverable_tax_calculated_on) as recoverable_tax_calculated_on'),
            )->active()->valid();

        $purchases = Purchase::query()->without(['attachments', 'user', 'suppliers'])
            ->whereBetween('date', [$start_date, $end_date])->select(
                DB::raw('SUM(grand_total) as grand_total'),
                DB::raw('SUM(total_tax_amount) as total_tax_amount'),
                DB::raw('SUM(recoverable_tax_amount) as recoverable_tax_amount'),
                DB::raw('SUM(recoverable_tax_calculated_on) as recoverable_tax_calculated_on'),
            )->active()->valid();

        $taxes = $sales->union($purchases)->get();
        return response()->json(['taxes' => $taxes,  'end_date' => $end_date, 'start_date' => $start_date]);
        // return response()->json(['sales' => $sales, 'purchases' => $purchases, 'end_date' => $end_date, 'start_date' => $start_date]);
    }
}
