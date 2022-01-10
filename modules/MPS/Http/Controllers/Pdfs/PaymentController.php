<?php

namespace Modules\MPS\Http\Controllers\Pdfs;

use Illuminate\Http\Request;
use Modules\MPS\Models\Payment;
use Illuminate\Support\Facades\App;
use Modules\MPS\Http\Controllers\Controller;

class PaymentController extends Controller
{
    public function __invoke(Request $request, Payment $payment)
    {
        $settings = json_decode(mps_config(), true);
        $payment->loadMissing(['account:id,name', 'location', 'payable', 'attachments']);
        unset($settings['svg_string']);
        // $payment->attributes = extra_attributes('payment');

        return match ($request->view) {
            'open'  => $this->pdf($settings, $payment),
            'html'  => $this->html($settings, $payment),
            default => $this->pdf($settings, $payment, true),
        };
    }

    protected function html($settings, $payment)
    {
        return view('mps::pdf.payment', compact('settings', 'payment'));
    }

    protected function pdf($settings, $payment, $download = false)
    {
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadView('mps::pdf.payment', compact('settings', 'payment'));
        return $download ? $pdf->download($payment->id . '.pdf') : $pdf->stream($payment->id . '.pdf');
    }
}
