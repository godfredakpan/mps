<?php

namespace Modules\MPS\Http\Controllers;

use Illuminate\Http\Request;
use Modules\MPS\Models\HallTable;
use Modules\MPS\Http\Requests\HallTableRequest;

class HallTableController extends Controller
{
    public function create()
    {
        return extra_attributes('table');
    }

    public function destroy(HallTable $table)
    {
        $table->del();
        return response(['success' => true]);
    }

    public function destroyMany(Request $request)
    {
        $count = $failed = 0;
        foreach (HallTable::whereIn('id', $request->ids)->get() as $table) {
            $table->del() ? $count++ : $failed++;
        }
        return response(['success' => !!$count, 'count' => $count, 'failed' => $failed]);
    }

    public function index(Request $request)
    {
        if ($request->hall) {
            return response()->json(HallTable::where('hall_id', $request->hall)->with(['hall:id,title'])->table(HallTable::$searchable));
        }
        return response()->json(HallTable::with(['hall:id,title'])->table(HallTable::$searchable));
    }

    public function show(HallTable $table)
    {
        $table->attributes = extra_attributes('table');
        return $table->load('hall:id,title');
    }

    public function store(HallTableRequest $request)
    {
        $table = HallTable::create($request->validated());
        return response(['success' => !!$table, 'data' => $table]);
    }

    public function update(HallTableRequest $request, HallTable $table)
    {
        $updated = $table->update($request->validated());
        return response(['success' => $updated, 'data' => $table->refresh()->load('hall:id,title')]);
    }
}
