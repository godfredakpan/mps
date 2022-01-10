<?php

namespace Modules\MPS\Http\Controllers\Pdfs;

use Illuminate\Http\Request;
use Modules\MPS\Models\Sale;
use Illuminate\Support\Facades\App;
use Modules\MPS\Http\Controllers\Controller;

class SaleController extends Controller
{
    public function __invoke(Request $request, Sale $sale)
    {
        $settings = json_decode(mps_config(), true);
        $sale->loadMissing([
            'location', 'customer', 'payments', 'items' => fn ($q) => $q->withAll(),
        ]);
        unset($settings['svg_string']);
        // $sale->attributes = extra_attributes('sale');

        return match ($request->view) {
            'open'  => $this->pdf($settings, $sale),
            'html'  => $this->html($settings, $sale),
            default => $this->pdf($settings, $sale, true),
        };
    }

    protected function html($settings, $sale)
    {
        return view('mps::pdf.sale', compact('settings', 'sale'));
    }

    protected function pdf($settings, $sale, $download = false)
    {
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadView('mps::pdf.sale', compact('settings', 'sale'));
        return $download ? $pdf->download($sale->id . '.pdf') : $pdf->stream($sale->id . '.pdf');
    }
}
