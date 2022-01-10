<?php

namespace Modules\MPS\Http\Controllers;

use Modules\MPS\Models\Sale;
use Modules\MPS\Models\Delivery;
// use Modules\MPS\Events\DeliveryEvent;
use Modules\MPS\Services\DeliveryService;
use Modules\MPS\Http\Requests\DeliveryRequest;

class DeliveryController extends Controller
{
    public function destroy(Delivery $delivery)
    {
        return response(['success' => $delivery->delete()]);
    }

    public function index()
    {
        return response()->json(Delivery::with('user:id,name')->table(Delivery::$searchable));
    }

    public function show(Delivery $delivery)
    {
        $delivery->attributes = extra_attributes('delivery');
        return $delivery->withItems();
    }

    public function store(DeliveryRequest $request, Sale $sale)
    {
        $form = $request->validated();

        $form['sale_id']     = $sale->id;
        $form['location_id'] = $sale->location_id;
        $delivery            = (new DeliveryService($form, new Delivery))->process()->save();
        $delivery->saveAttachments($request->file('attachments'));
        // event(new DeliveryEvent($delivery, 'created'));
        return response(['success' => true, 'data' => $delivery]);
    }

    public function update(DeliveryRequest $request, Delivery $delivery)
    {
        $form   = $request->validated();
        $update = (new DeliveryService($form, $delivery, true))->process()->save();
        $update->saveAttachments($request->file('attachments'));
        if ($request->stay == 1) {
            $update = $update->fresh()->withItems();
        }
        return response(['success' => !!$update, 'data' => $update]);
    }
}
