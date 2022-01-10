<?php

namespace Modules\MPS\Http\Controllers;

use Illuminate\Http\Request;
use Modules\MPS\Models\Sale;
use Modules\MPS\Events\SaleEvent;
use Modules\MPS\Services\OrderService;
use Modules\MPS\Http\Requests\SaleRequest;
use Modules\MPS\Services\OrderPaymentService;

class SaleController extends Controller
{
    public function create()
    {
        return extra_attributes('sale');
    }

    public function destroy(Sale $sale)
    {
        // $deleted = $sale->del();
        return response([
            'success' => $sale->del(),
            'message' => __('record_deleted'),
            'error'   => __choice('delete_error', ['relations' => trans_choice('payment', 2) . ' ']),
        ]);
    }

    public function destroyMany(Request $request)
    {
        $count = $failed = 0;
        foreach (Sale::whereIn('id', $request->ids)->get() as $sale) {
            $sale->del() ? $count++ : $failed++;
        }
        return response(['success' => !!$count, 'count' => $count, 'failed' => $failed]);
    }

    public function download(Sale $sale)
    {
        $settings = json_decode(mps_config(), true);
        unset($settings['svg_string'], $settings['json_string']);
        $sale->loadMissing(['location', 'customer', 'payments', 'items' => fn ($q) => $q->withAll()]);
        return app('dompdf.wrapper')->loadView('mps::pdf.sale', compact('settings', 'sale'))->download($sale->id . '.pdf');
    }

    public function email(Sale $sale)
    {
        if (safe_email($sale->customer->email)) {
            $sale->customer->notify(new \Modules\MPS\Notifications\SaleCreated($sale));
            return response(['success' => true]);
        }
        return response(['success' => false, 'lang_key' => 'email_failed'], 422);
    }

    public function index()
    {
        return response()->json(Sale::table(Sale::$searchable));
    }

    public function payments(Sale $sale)
    {
        return $sale->payments;
    }

    public function show(Request $request, Sale $sale)
    {
        $sale->attributes = extra_attributes('sale');
        return $sale->loadMissing([
            'location', 'customer', 'payments', 'items' => fn ($q) => $q->withAll(),
        ]);
    }

    public function store(SaleRequest $request)
    {
        $form = $request->validated();
        $sale = (new OrderService($form, new Sale()))->process()->save();
        $sale->saveAttachments($request->file('attachments'));
        (new OrderPaymentService($request, $sale))->process();
        event(new SaleEvent($sale, 'created'));
        if ($request->with == 'items') {
            $sale->load([
                'location', 'customer', 'payments', 'items' => fn ($q) => $q->withAll(),
            ]);
        }
        return response(['success' => !!$sale, 'data' => $sale]);
    }

    public function toggleVoid(Sale $sale)
    {
        $sale->void = !$sale->void;
        $sale->save();
        return response(['success' => true, 'void' => $sale->void]);
    }

    public function update(SaleRequest $request, Sale $sale)
    {
        $form   = $request->validated();
        $old    = $sale->load(['items', 'customer', 'items.stock', 'items.portions'])->replicate();
        $update = (new OrderService($form, $sale, true))->process()->save();
        $update->saveAttachments($request->file('attachments'));
        event(new SaleEvent($update, 'updated', $old));
        if ($request->stay == 1) {
            $update = $update->fresh()->withAll();
        }
        return response(['success' => !!$update, 'data' => $update]);
    }
}
