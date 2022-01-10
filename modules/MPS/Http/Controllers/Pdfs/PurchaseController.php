<?php

namespace Modules\MPS\Http\Controllers\Pdfs;

use Illuminate\Http\Request;
use Modules\MPS\Models\Purchase;
use Illuminate\Support\Facades\App;
use Modules\MPS\Http\Controllers\Controller;

class PurchaseController extends Controller
{
    public function __invoke(Request $request, Purchase $purchase)
    {
        $settings = json_decode(mps_config(), true);
        $purchase->loadMissing([
            'location', 'supplier', 'payments', 'items' => fn ($q) => $q->withAll(),
        ]);
        unset($settings['svg_string']);
        // $purchase->attributes = extra_attributes('purchase');

        return match ($request->view) {
            'open'  => $this->pdf($settings, $purchase),
            'html'  => $this->html($settings, $purchase),
            default => $this->pdf($settings, $purchase, true),
        };
    }

    protected function html($settings, $purchase)
    {
        return view('mps::pdf.purchase', compact('settings', 'purchase'));
    }

    protected function pdf($settings, $purchase, $download = false)
    {
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadView('mps::pdf.purchase', compact('settings', 'purchase'));
        return $download ? $pdf->download($purchase->id . '.pdf') : $pdf->stream($purchase->id . '.pdf');
    }
}
