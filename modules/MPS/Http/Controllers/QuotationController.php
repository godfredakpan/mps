<?php

namespace Modules\MPS\Http\Controllers;

use Illuminate\Http\Request;
use Modules\MPS\Models\Quotation;
use Modules\MPS\Services\OrderService;
use Modules\MPS\Http\Requests\QuotationRequest;

class QuotationController extends Controller
{
    public function create()
    {
        return extra_attributes('quotation');
    }

    public function destroy(Quotation $quotation)
    {
        $quotation->del();
        return response(['success' => true]);
    }

    public function destroyMany(Request $request)
    {
        $count = $failed = 0;
        foreach (Quotation::whereIn('id', $request->ids)->get() as $quotation) {
            $quotation->del() ? $count++ : $failed++;
        }
        return response(['success' => !!$count, 'count' => $count, 'failed' => $failed]);
    }

    public function download(Quotation $quotation)
    {
        $settings = json_decode(mps_config(), true);
        unset($settings['svg_string'], $settings['json_string']);
        $quotation->loadMissing(['location', 'customer', 'items' => fn ($q) => $q->withAll()]);
        return app('dompdf.wrapper')->loadView('mps::pdf.quotation', compact('settings', 'quotation'))->download($quotation->id . '.pdf');
    }

    public function email(Quotation $quotation)
    {
        if (safe_email($quotation->customer->email)) {
            $quotation->customer->notify(new \Modules\MPS\Notifications\QuotationCreated($quotation));
            return response(['success' => true]);
        }
        return response(['success' => false, 'lang_key' => 'email_failed'], 422);
    }

    public function index()
    {
        return response()->json(Quotation::table(Quotation::$searchable));
    }

    public function show(Request $request, Quotation $quotation)
    {
        $quotation->attributes = extra_attributes($request->attr == 'sale' ? 'sale' : 'quotation');
        return $quotation->loadMissing([
            'location', 'customer', 'items' => fn ($q) => $q->withAll(),
        ]);
    }

    public function store(QuotationRequest $request)
    {
        $form      = $request->validated();
        $quotation = (new OrderService($form, new Quotation()))->process()->save();
        $quotation->saveAttachments($request->file('attachments'));
        return response(['success' => !!$quotation, 'data' => $quotation]);
    }

    public function update(QuotationRequest $request, Quotation $quotation)
    {
        $form   = $request->validated();
        $update = (new OrderService($form, $quotation, true))->process()->save();
        $update->saveAttachments($request->file('attachments'));
        if ($request->stay == 1) {
            $update = $update->fresh()->withAll();
        }
        return response(['success' => !!$update, 'data' => $update]);
    }
}
