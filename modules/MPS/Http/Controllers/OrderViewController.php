<?php

namespace Modules\MPS\Http\Controllers;

use Illuminate\Http\Request;
use Modules\MPS\Models\Sale;
use Modules\MPS\Models\Payment;
use Modules\MPS\Models\Purchase;
use Modules\MPS\Models\Quotation;
use Modules\MPS\Models\ReturnOrder;
use App\Http\Controllers\Controller;

class OrderViewController extends Controller
{
    public function index($view, $hash)
    {
        return redirect()->away(route('mps') . '#/views?' . $view . '=' . $hash);
    }

    public function payment($hash)
    {
        $payment = Payment::withoutGlobalScopes()
            ->with(['location', 'payable'])
            ->where('hash', $hash)->firstOrFail();

        $payment->attributes = extra_attributes('payment');
        return response()->json($payment);
    }

    public function purchase(Request $request, $hash)
    {
        $purchase = Purchase::withoutGlobalScopes()
            ->with(['location', 'supplier', 'payments', 'items' => fn ($q) => $q->withAll()])
            ->where('hash', $hash)->firstOrFail();

        $purchase->attributes = extra_attributes('purchase');
        return response()->json($purchase);
    }

    public function quotation($hash)
    {
        $quotation = Quotation::withoutGlobalScopes()
            ->with(['location', 'customer', 'items' => fn ($q) => $q->withAll()])
            ->where('hash', $hash)->firstOrFail();

        // sale || quotation
        $quotation->attributes = extra_attributes('quotation');
        return response()->json($quotation);
    }

    public function returnOrder($hash)
    {
        $return_order = ReturnOrder::withoutGlobalScopes()
            ->with(['location', 'customer', 'supplier', 'payments', 'items' => fn ($q) => $q->withAll()])
            ->where('hash', $hash)->firstOrFail();

        $return_order->attributes = extra_attributes('return_order');
        return response()->json($return_order);
    }

    public function sale($hash)
    {
        $sale = Sale::withoutGlobalScopes()
            ->with(['location', 'customer', 'payments', 'items' => fn ($q) => $q->withAll()])
            ->where('hash', $hash)->firstOrFail();

        $sale->attributes = extra_attributes('sale');
        return response()->json($sale);
    }
}
