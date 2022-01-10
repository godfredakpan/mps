<?php

namespace Modules\MPS\Http\Controllers;

use Illuminate\Http\Request;
use Modules\MPS\Models\RecurringSale;
use Modules\MPS\Services\OrderService;
use Modules\MPS\Http\Requests\RecurringSaleRequest;

class RecurringSaleController extends Controller
{
    public function create()
    {
        return extra_attributes('sale');
    }

    public function destroy(RecurringSale $recurring_sale)
    {
        $recurring_sale->del();
        return response(['success' => true]);
    }

    public function destroyMany(Request $request)
    {
        $count = $failed = 0;
        foreach (RecurringSale::whereIn('id', $request->ids)->get() as $recurring_sale) {
            $recurring_sale->del() ? $count++ : $failed++;
        }
        return response(['success' => !!$count, 'count' => $count, 'failed' => $failed]);
    }

    public function index(Request $request)
    {
        if ($request->in_days) {
            return response()->json(RecurringSale::dueDays($request->in_days)->table(RecurringSale::$searchable));
        }
        return response()->json(RecurringSale::table(RecurringSale::$searchable));
    }

    public function show(RecurringSale $recurring_sale)
    {
        $recurring_sale->attributes = extra_attributes('sale');
        return $recurring_sale->loadMissing([
            'location', 'customer', 'items' => fn ($q) => $q->withRecurring(),
        ]);
    }

    public function store(RecurringSaleRequest $request)
    {
        $form = $request->validated();
        $sale = (new OrderService($form, new RecurringSale))->process()->save();
        $sale->saveAttachments($request->file('attachments'));
        return response(['success' => !!$sale, 'data' => $sale]);
    }

    public function update(RecurringSaleRequest $request, RecurringSale $recurring_sale)
    {
        $form   = $request->validated();
        $update = (new OrderService($form, $recurring_sale, true))->process()->save();
        $update->saveAttachments($request->file('attachments'));
        if ($request->stay == 1) {
            $update = $update->fresh()->load('items');
        }
        return response(['success' => !!$update, 'data' => $update]);
    }
}
