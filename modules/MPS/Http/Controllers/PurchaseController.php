<?php

namespace Modules\MPS\Http\Controllers;

use Illuminate\Http\Request;
use Modules\MPS\Models\Purchase;
use Modules\MPS\Events\PurchaseEvent;
use Modules\MPS\Services\OrderService;
use Modules\MPS\Services\OrderPaymentService;
use Modules\MPS\Http\Requests\PurchaseRequest;

class PurchaseController extends Controller
{
    public function create()
    {
        return extra_attributes('purchase');
    }

    public function destroy(Purchase $purchase)
    {
        // $purchase->del();
        return response([
            'success' => $purchase->del(),
            'message' => __('record_deleted'),
            'error'   => __choice('delete_error', ['relations' => trans_choice('payment', 2) . ' ']),
        ]);
    }

    public function destroyMany(Request $request)
    {
        $count = $failed = 0;
        foreach (Purchase::whereIn('id', $request->ids)->get() as $purchase) {
            $purchase->del() ? $count++ : $failed++;
        }
        return response(['success' => !!$count, 'count' => $count, 'failed' => $failed]);
    }

    public function download(Purchase $purchase)
    {
        $settings = json_decode(mps_config(), true);
        unset($settings['svg_string'], $settings['json_string']);
        $purchase->loadMissing(['location', 'supplier', 'payments', 'items' => fn ($q) => $q->withAll()]);
        return app('dompdf.wrapper')->loadView('mps::pdf.purchase', compact('settings', 'purchase'))->download($purchase->id . '.pdf');
    }

    public function email(Purchase $purchase)
    {
        if (safe_email($purchase->supplier->email)) {
            $purchase->supplier->notify(new \Modules\MPS\Notifications\PurchaseCreated($purchase));
            return response(['success' => true]);
        }
        return response(['success' => false, 'lang_key' => 'email_failed'], 422);
    }

    public function index()
    {
        return response()->json(Purchase::table(Purchase::$searchable));
    }

    public function payments(Purchase $purchase)
    {
        return $purchase->payments;
    }

    public function show(Purchase $purchase)
    {
        $purchase->attributes = extra_attributes('purchase');
        return $purchase->loadMissing([
            'location', 'supplier', 'payments', 'items' => fn ($q) => $q->withAll(),
        ]);
    }

    public function store(PurchaseRequest $request)
    {
        $form     = $request->validated();
        $purchase = (new OrderService($form, new Purchase()))->process()->save();
        $purchase->saveAttachments($request->file('attachments'));
        (new OrderPaymentService($request, $purchase))->process();
        event(new PurchaseEvent($purchase, 'created'));
        return response(['success' => !!$purchase, 'data' => $purchase]);
    }

    public function toggleVoid(Purchase $purchase)
    {
        $purchase->void = !$purchase->void;
        $purchase->save();
        return response(['success' => true, 'void' => $purchase->void]);
    }

    public function update(PurchaseRequest $request, Purchase $purchase)
    {
        $form   = $request->validated();
        $old    = $purchase->load(['items', 'supplier', 'items.stock'])->replicate();
        $update = (new OrderService($form, $purchase, true))->process()->save();
        $update->saveAttachments($request->file('attachments'));
        event(new PurchaseEvent($update, 'updated', $old));
        if ($request->stay == 1) {
            $update = $update->fresh()->withAll();
        }
        return response(['success' => !!$update, 'data' => $update]);
    }
}
