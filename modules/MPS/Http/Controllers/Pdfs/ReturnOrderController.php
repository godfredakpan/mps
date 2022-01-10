<?php

namespace Modules\MPS\Http\Controllers\Pdfs;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Modules\MPS\Models\ReturnOrder;
use Modules\MPS\Http\Controllers\Controller;

class ReturnOrderController extends Controller
{
    public function __invoke(Request $request, ReturnOrder $return_order)
    {
        $settings = json_decode(mps_config(), true);
        $return_order->loadMissing([
            'location', 'customer', 'payments', 'items' => fn ($q) => $q->withAll(),
        ]);
        unset($settings['svg_string']);
        // $return_order->attributes = extra_attributes('return_order');

        return match ($request->view) {
            'open'  => $this->pdf($settings, $return_order),
            'html'  => $this->html($settings, $return_order),
            default => $this->pdf($settings, $return_order, true),
        };
    }

    protected function html($settings, $return_order)
    {
        return view('mps::pdf.return_order', compact('settings', 'return_order'));
    }

    protected function pdf($settings, $return_order, $download = false)
    {
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadView('mps::pdf.return_order', compact('settings', 'return_order'));
        return $download ? $pdf->download($return_order->id . '.pdf') : $pdf->stream($return_order->id . '.pdf');
    }
}
