<?php

namespace Modules\MPS\Http\Controllers\Reports;

use Illuminate\Http\Request;
use Modules\MPS\Models\Payment;
use Illuminate\Support\Facades\DB;
use Modules\MPS\Http\Controllers\Controller;

class PaymentReportController extends Controller
{
    public function index(Request $request)
    {
        $end_date   = $request->end_date ?: now()->endOfDay();
        $start_date = $request->start_date ?: now()->subMonths(3)->startOfDay();

        $payments = $this->prepareRequest($request)->select(
            DB::raw('COUNT(id) as total'),
            DB::raw('SUM(amount) as amount'),
            DB::raw('count(case when received = 1 then 1 end) as received'),
            DB::raw('SUM(case when received = 1 then amount end) as received_amount'),
            DB::raw('count(case when received IS NULL OR received = 0 then 1 end) as due'),
            DB::raw('SUM(case when received IS NULL OR received = 0 then amount end) as due_amount'),
            DB::raw("SUM(case when received = 1 AND payable_type LIKE '%Customer' then amount end) as customer_amount"),
            DB::raw("SUM(case when received = 1 AND payable_type LIKE '%Supplier' then amount end) as supplier_amount"),
        )->first();
        return response()->json(['payments' => $payments, 'end_date' => $end_date, 'start_date' => $start_date]);
    }

    public function table(Request $request)
    {
        $payments = $this->prepareRequest($request);
        return response()->json($payments->table([]));
    }

    private function prepareRequest(Request $request)
    {
        $payments   = Payment::query();
        $end_date   = $request->end_date ?: now()->endOfDay();
        $start_date = $request->start_date ?: now()->subMonths(3)->startOfDay();
        $payments->whereBetween('created_at', [$start_date, $end_date]);

        if ($request->customer_id) {
            $payments->where('payable_id', $request->input('customer_id'))->where('payable_type', 'Modules\MPS\Models\Customer');
        }
        if ($request->supplier_id) {
            $payments->where('payable_id', $request->input('supplier_id'))->where('payable_type', 'Modules\MPS\Models\Supplier');
        }
        if ($request->user_id) {
            $payments->where('user_id', $request->input('user_id'));
        }
        if ($request->custom_fields) {
            $payments->where('extra_attributes', 'like', "%{$request->input('custom_fields')}%");
        }
        if ($request->received != null && ($request->received == 0 || $request->received == 1)) {
            if ($request->received == 1) {
                $payments->received();
            } else {
                $payments->due();
            }
        }

        return $payments;
    }
}
