<?php

namespace Modules\MPS\Http\Controllers;

use Illuminate\Http\Request;
use Modules\MPS\Models\Item;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Modules\MPS\Http\Requests\ItemRequest;

class ItemController extends Controller
{
    public function create()
    {
        return extra_attributes('item');
    }

    public function destroy(Item $item)
    {
        return response([
            'success' => $item->del(),
            'message' => __('record_deleted'),
            'error'   => __choice('delete_error', ['relations' => trans_choice('sale', 2) . ', ' . trans_choice('purchase', 2) . ', ' . trans_choice('modifier', 2) . ', ' . trans_choice('portion', 2) . ' ']),
        ]);
    }

    public function destroyMany(Request $request)
    {
        $count = $failed = 0;
        foreach (Item::whereIn('id', $request->ids)->get() as $item) {
            $item->del() ? $count++ : $failed++;
        }
        return response(['success' => !!$count, 'count' => $count, 'failed' => $failed]);
    }

    // TODO
    public function export()
    {
        return Excel::download(new \Modules\MPS\Exports\ItemExport, 'items.xlsx');
    }

    // TODO
    public function import()
    {
        Excel::import(new \Modules\MPS\Imports\ItemImport, 'items.xlsx');
        return response()->json(['success' => true]);
    }

    public function index(Request $request)
    {
        if ($cId = $request->category) {
            return response()->json(Item::pos()->withAll()
                ->whereHas('categories', function ($query) use ($cId) {
                    $query->where('id', $cId);
                })->orderBy('name')->get());
        }
        $items = Item::withList()->ofType($request->type ?? 'standard');
        return response()->json($items->table(Item::$searchable));
    }

    public function show(Item $item)
    {
        $item->attributes = extra_attributes('item');
        return response()->json($item->loadAll());
    }

    public function store(ItemRequest $request)
    {
        $item = (new Item())->saveItem($request->validated());
        return response()->json(['success' => !!$item, 'data' => $item]);
    }

    public function update(ItemRequest $request, Item $item)
    {
        $updated = $item->saveItem($request->validated(), true);
        return response(['success' => $updated, 'data' => $item]);
    }
}
