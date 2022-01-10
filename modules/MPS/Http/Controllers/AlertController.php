<?php

namespace Modules\MPS\Http\Controllers;

use Illuminate\Http\Request;
use Modules\MPS\Models\Item;
use Modules\MPS\Models\User;
use Modules\MPS\Models\Expense;
use Modules\MPS\Models\Payment;
use Modules\MPS\Models\Customer;
use Modules\MPS\Models\Supplier;
use App\Http\Controllers\Controller;
use Modules\MPS\Models\PurchaseItem;
use Modules\MPS\Models\RecurringSale;

class AlertController extends Controller
{
    public function expiring(Request $request)
    {
        $purchaseItems = PurchaseItem::whereNotNull('expiry_date')->where('expiry_date', '<', now()->addDays(90));
        return response()->json($purchaseItems->table(PurchaseItem::$searchable));
    }

    public function index(Request $request)
    {
        $payments        = Payment::due()->count();
        $payment_reviews = Payment::review()->count();
        $expenses        = Expense::requireApproval()->count();
        $recurring_soon  = RecurringSale::active()->dueDays(7)->count();
        $expiry_alert    = PurchaseItem::whereNotNull('expiry_date')->where('expiry_date', '<', now()->addDays(90))->count();
        $quantity_alert  = Item::whereHas('stock', function ($q) {
            $q->ofLocation()->whereNotNull('alert_quantity')->whereColumn('quantity', '<=', 'alert_quantity');
        })->count();
        $customers = Customer::whereNotNull('due_limit')->whereHas('journal', function ($q) {
            $q->whereColumn('balance', '>=', 'customers.due_limit');
        })->count();
        $suppliers = Supplier::whereNotNull('due_limit')->whereHas('journal', function ($q) {
            $q->whereColumn('balance', '>=', 'suppliers.due_limit');
        })->count();

        return response()->json([
            'expenses'        => $expenses,
            'payments'        => $payments,
            'customers'       => $customers,
            'suppliers'       => $suppliers,
            'expiry_alert'    => $expiry_alert,
            'quantity_alert'  => $quantity_alert,
            'recurring_soon'  => $recurring_soon,
            'payment_reviews' => $payment_reviews,
            'notifications'   => $request->user() ? User::find($request->user()->id)->unreadNotifications()->count() : 0,
        ]);
    }

    public function quantity(Request $request)
    {
        $items = Item::withList()->ofType('standard')
        // ->where(function ($query) {
        //     $query->where('alert_quantity', 0)->orWhereNotNull('alert_quantity');
        // })
        ->whereHas('stock', function ($q) {
            $q->ofLocation()->whereColumn('quantity', '>=', 'alert_quantity');
        });
        return response()->json($items->table(Item::$searchable));
    }
}
