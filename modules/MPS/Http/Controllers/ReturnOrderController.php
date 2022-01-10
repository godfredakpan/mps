<?php

namespace Modules\MPS\Http\Controllers;

use Illuminate\Http\Request;
use Modules\MPS\Models\ReturnOrder;
use Modules\MPS\Services\OrderService;
use Modules\MPS\Events\ReturnOrderEvent;
use Modules\MPS\Services\OrderPaymentService;
use Modules\MPS\Http\Requests\ReturnOrderRequest;

class ReturnOrderController extends Controller
{
    public function create()
    {
        return extra_attributes('return_order');
    }

    public function destroy(ReturnOrder $return_order)
    {
        $return_order->del();
        return response(['success' => true]);
    }

    public function destroyMany(Request $request)
    {
        $count = $failed = 0;
        foreach (ReturnOrder::whereIn('id', $request->ids)->get() as $return_order) {
            $return_order->del() ? $count++ : $failed++;
        }
        return response(['success' => !!$count, 'count' => $count, 'failed' => $failed]);
    }

    public function download(ReturnOrder $return_order)
    {
        $settings = json_decode(mps_config(), true);
        unset($settings['svg_string'], $settings['json_string']);
        $return_order->loadMissing(['location', 'customer', 'supplier', 'payments', 'items' => fn ($q) => $q->withAll()]);
        return app('dompdf.wrapper')->loadView('mps::pdf.return_order', compact('settings', 'return_order'))->download($return_order->id . '.pdf');
    }

    public function email(ReturnOrder $return_order)
    {
        if (safe_email($return_order->customer->email)) {
            $return_order->customer->notify(new \Modules\MPS\Notifications\ReturnOrderCreated($return_order));
            return response(['success' => true]);
        }
        return response(['success' => false, 'lang_key' => 'email_failed'], 422);
    }

    public function index()
    {
        return response()->json(ReturnOrder::table(ReturnOrder::$searchable));
    }

    public function payments(ReturnOrder $return_order)
    {
        return $return_order->payments;
    }

    public function show(ReturnOrder $return_order)
    {
        $return_order->attributes = extra_attributes('return_order');
        return $return_order->loadMissing([
            'location', 'customer', 'supplier', 'payments', 'items' => fn ($q) => $q->withAll(),
        ]);
    }

    public function store(ReturnOrderRequest $request)
    {
        $form         = $request->validated();
        $return_order = (new OrderService($form, new ReturnOrder()))->process()->save();
        $return_order->saveAttachments($request->file('attachments'));
        (new OrderPaymentService($request, $return_order))->process();
        event(new ReturnOrderEvent($return_order, 'created'));
        return response(['success' => !!$return_order, 'data' => $return_order]);
    }

    public function update(ReturnOrderRequest $request, ReturnOrder $return_order)
    {
        $form   = $request->validated();
        $old    = $return_order->load(['items', 'customer', 'supplier', 'items.stock'])->replicate();
        $update = (new OrderService($form, $return_order, true))->process()->save();
        $update->saveAttachments($request->file('attachments'));
        event(new ReturnOrderEvent($update, 'updated', $old));
        if ($request->stay == 1) {
            $update = $update->fresh()->withAll();
        }
        return response(['success' => !!$update, 'data' => $update]);
    }
}
