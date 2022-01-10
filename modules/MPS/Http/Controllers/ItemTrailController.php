<?php

namespace Modules\MPS\Http\Controllers;

use Illuminate\Http\Request;
use Modules\MPS\Models\Item;
use Modules\MPS\Models\StockTrail;

class ItemTrailController extends Controller
{
    public function index(Request $request, Item $item)
    {
        $end_date   = $request->end_date ?: now()->endOfDay();
        $start_date = $request->start_date ?: now()->subMonths(3)->startOfDay();

        $transactions = $this->prepareRequest($request, $item);
        $stock        = [
            'close_stock' => $transactions->where('created_at', '<=', $end_date)->sum('quantity') ?: 0,
            'start_stock' => $transactions->where('created_at', '<=', $start_date)->sum('quantity') ?: 0,
        ];
        return response()->json(['stock' => $stock, 'end_date' => $end_date, 'start_date' => $start_date]);
    }

    public function table(Request $request, Item $item)
    {
        $trails = $this->prepareRequest($request, $item);
        return response()->json($trails->table(StockTrail::$searchable));
    }

    private function prepareRequest(Request $request, Item $item)
    {
        $trails     = $item->stockTrails();
        $end_date   = $request->end_date ?: now()->endOfDay();
        $start_date = $request->start_date ?: now()->subMonths(3)->startOfDay();
        $trails->whereBetween('created_at', [$start_date, $end_date]);

        if ($request->location_id) {
            $trails->where('location_id', $request->input('location_id'));
        }

        return $trails;
    }
}
