<?php

namespace Modules\MPS\Http\Controllers\Pdfs;

use Illuminate\Http\Request;
use Modules\MPS\Models\Quotation;
use Illuminate\Support\Facades\App;
use Modules\MPS\Http\Controllers\Controller;

class QuotationController extends Controller
{
    public function __invoke(Request $request, Quotation $quotation)
    {
        $settings = json_decode(mps_config(), true);
        $quotation->loadMissing(['location', 'customer', 'items' => fn ($q) => $q->withAll()]);
        unset($settings['svg_string']);
        // $quotation->attributes = extra_attributes('quotation');

        return match ($request->view) {
            'open'  => $this->pdf($settings, $quotation),
            'html'  => $this->html($settings, $quotation),
            default => $this->pdf($settings, $quotation, true),
        };
    }

    protected function html($settings, $quotation)
    {
        return view('mps::pdf.quotation', compact('settings', 'quotation'));
    }

    protected function pdf($settings, $quotation, $download = false)
    {
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadView('mps::pdf.quotation', compact('settings', 'quotation'));
        return $download ? $pdf->download($quotation->id . '.pdf') : $pdf->stream($quotation->id . '.pdf');
    }
}
