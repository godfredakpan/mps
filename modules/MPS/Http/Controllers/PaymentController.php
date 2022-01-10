<?php

namespace Modules\MPS\Http\Controllers;

use Illuminate\Http\Request;
use Modules\MPS\Models\Payment;
use Modules\MPS\Models\Customer;
use Modules\MPS\Models\Supplier;
use Modules\MPS\Http\Requests\PaymentRequest;

class PaymentController extends Controller
{
    public function create()
    {
        return extra_attributes('payment');
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();
        return response(['success' => true]);
    }

    public function download(Payment $payment)
    {
        $settings = json_decode(mps_config(), true);
        unset($settings['svg_string'], $settings['json_string']);
        $payment->loadMissing(['account:id,name', 'location', 'payable']);
        return app('dompdf.wrapper')->loadView('mps::pdf.payment', compact('settings', 'payment'))->download($payment->id . '.pdf');
    }

    public function email(Payment $payment)
    {
        if (safe_email($payment->payable->email)) {
            if ($payment->received) {
                $payment->payable->notify(new \Modules\MPS\Notifications\PaymentReceived($payment));
            } else {
                $payment->payable->notify(new \Modules\MPS\Notifications\PaymentCreated($payment));
            }
            return response(['success' => true]);
        }
        return response(['success' => false, 'lang_key' => 'email_failed'], 422);
    }

    public function index(Request $request)
    {
        if ($request->review == 1) {
            return response()->json(Payment::ofLocation()->review()->table(Payment::$searchable));
        } elseif ($request->due == 1) {
            return response()->json(Payment::ofLocation()->due()->table(Payment::$searchable));
        }
        return response()->json(Payment::ofLocation()->table(Payment::$searchable));
    }

    public function review(Payment $payment)
    {
        $update = $payment->update(['reviewed_by' => auth()->id(), 'received' => 1]);
        if ($payment->sale_id && $payment->sale->shop == 1) {
            event(new \Modules\MPS\Events\SaleEvent($payment->sale, 'updated'));
        }
        return response(['success' => $update, 'data' => $payment->refresh()]);
    }

    public function show(Payment $payment)
    {
        $payment->attributes = extra_attributes('payment');
        return $payment->loadMissing([
            'location', 'payable',
        ]);
    }

    public function store(PaymentRequest $request)
    {
        $data  = $request->validated();
        $payer = $this->payer($data);
        $data  = $this->process($request, $data, $payer);
        if (isset($data['success']) && !$data['success']) {
            return response()->json($data);
        }
        $payment = $payer->payments()->create($data);
        $payment->saveAttachments($request->file('attachments'));
        return response(['success' => !!$payment, 'data' => $payment]);
    }

    public function update(PaymentRequest $request, Payment $payment)
    {
        $data   = $request->validated();
        $update = $payment->update($data);
        $payment->saveAttachments($request->file('attachments'));
        return response(['success' => $update, 'data' => $payment->refresh()]);
    }

    private function payer($data)
    {
        if (isset($data['customer_id']) && !empty($data['customer_id'])) {
            $payer = Customer::findOrFail($data['customer_id']);
        } else {
            $payer = Supplier::findOrFail($data['supplier_id']);
        }
        return $payer;
    }

    private function process($request, $data, $payer)
    {
        $gateway = env('CARD_GATEWAY');
        if ($gateway && $data['gateway'] && $gateway == $data['gateway']) {
            $data = (new \Modules\MPS\Services\Payment($gateway, demo()))->processStoreRequest($request, $data, $payer);
        }
        return $data;
    }
}
