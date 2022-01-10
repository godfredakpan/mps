<?php

namespace Modules\MPS\Http\Controllers\Reports;

use Illuminate\Http\Request;
use Modules\MPS\Models\Item;
use Modules\MPS\Models\SaleItem;
use Illuminate\Database\Eloquent\Builder;
use Modules\MPS\Http\Controllers\Controller;

class ItemReportController extends Controller
{
    public function index(Request $request)
    {
        $end_date   = $request->end_date ?: now()->endOfDay();
        $start_date = $request->start_date ?: now()->subMonths(3)->startOfDay();

        $items = $this->prepareRequest($request, $start_date, $end_date);
        return response()->json(['table' => $items->table([]), 'end_date' => $end_date, 'start_date' => $start_date]);
    }

    public function top(Request $request)
    {
        $end_date   = $request->end_date ?: now()->endOfDay();
        $start_date = $request->start_date ?: now()->subMonths(3)->startOfDay();

        $items = $this->prepareTopItemsRequest($request, $start_date, $end_date);
        return response()->json(['table' => $items->table([]), 'end_date' => $end_date, 'start_date' => $start_date]);
    }

    private function prepareRequest(Request $request, $start_date, $end_date, $order = null)
    {
        $items = Item::query()->select(['id', 'created_at', 'code', 'name', 'alt_name']);
        $items->with([
            'purchaseItems' => fn ($q) => $q->select(['item_id', 'quantity', 'subtotal'])->whereBetween('created_at', [$start_date, $end_date]),
            'saleItems'     => fn ($q)     => $q->select(['item_id', 'quantity', 'subtotal'])->whereBetween('created_at', [$start_date, $end_date]),
        ]);

        if ($request->start_created_at && $request->end_created_at) {
            $items->whereBetween('created_at', [$request->input('start_created_at'), $request->input('end_created_at')]);
        }
        if ($request->code) {
            $items->where('code', $request->input('code'));
        }
        if ($request->name) {
            $items->where('name', 'like', "%{$request->input('name')}%");
        }
        if ($request->category_id) {
            $items->whereHas('categories', fn (Builder $q) => $q->where('category_id', $request->input('category_id')));
        }
        if ($request->custom_fields) {
            $items->where('extra_attributes', 'like', "%{$request->input('custom_fields')}%");
        }

        return $items;
    }

    private function prepareTopItemsRequest(Request $request, $start_date, $end_date, $order = null)
    {
        $items  = Item::query()->select(['id', 'code', 'name', 'alt_name']);
        $column = SaleItem::query()->selectRaw('SUM(quantity)')->whereColumn('item_id', 'items.id');
        $column->whereHas('sale', fn (Builder $q) => $q->whereBetween('created_at', [$start_date, $end_date]));

        if ($request->customer_id) {
            $column->whereHas('sale', fn (Builder $q) => $q->where('customer_id', $request->input('customer_id')));
        }
        if ($request->user_id) {
            $column->whereHas('sale', fn (Builder $q) => $q->where('user_id', $request->input('user_id')));
        }
        if ($request->category_id) {
            $items->whereHas('categories', fn (Builder $q) => $q->where('category_id', $request->input('category_id')));
        }

        $column->take(1);
        $items->addSelect(['sold' => $column]);
        $items->orderByDesc($column);
        return $items;
    }
}
