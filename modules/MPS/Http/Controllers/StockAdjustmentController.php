<?php

namespace Modules\MPS\Http\Controllers;

use Modules\MPS\Services\OrderService;
use Modules\MPS\Models\StockAdjustment;
use Modules\MPS\Events\StockAdjustmentEvent;
use Modules\MPS\Http\Requests\StockAdjustmentRequest;

class StockAdjustmentController extends Controller
{
    public function create()
    {
        return extra_attributes('stock_adjustment');
    }

    public function destroy(StockAdjustment $adjustment)
    {
        $adjustment->delete();
        return response(['success' => true]);
    }

    public function index()
    {
        return response()->json(StockAdjustment::with('user:id,name')->table(StockAdjustment::$searchable));
    }

    public function show(StockAdjustment $adjustment)
    {
        return $adjustment->withItems();
    }

    public function store(StockAdjustmentRequest $request)
    {
        $form       = $request->validated();
        $adjustment = (new OrderService($form, new StockAdjustment))->process()->save();
        $adjustment->saveAttachments($request->file('attachments'));
        event(new StockAdjustmentEvent($adjustment, 'created'));
        return response(['success' => !!$adjustment, 'data' => $adjustment]);
    }

    public function update(StockAdjustmentRequest $request, StockAdjustment $adjustment)
    {
        $form   = $request->validated();
        $old    = $adjustment->load(['items', 'items.stock'])->replicate();
        $update = (new OrderService($form, $adjustment, true))->process()->save();
        $update->saveAttachments($request->file('attachments'));
        event(new StockAdjustmentEvent($update, 'updated', $old));
        if ($request->stay == 1) {
            $update = $update->fresh()->withAdjustment();
        }
        return response(['success' => !!$update, 'data' => $update]);
    }
}
