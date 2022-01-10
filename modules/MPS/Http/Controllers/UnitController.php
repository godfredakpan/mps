<?php

namespace Modules\MPS\Http\Controllers;

use Illuminate\Http\Request;
use Modules\MPS\Models\Unit;
use Modules\MPS\Http\Requests\UnitRequest;

class UnitController extends Controller
{
    public function destroy(Unit $unit)
    {
        $unit->del();
        return response(['success' => true]);
    }

    public function destroyMany(Request $request)
    {
        $count = $failed = 0;
        foreach (Unit::whereIn('id', $request->ids)->get() as $unit) {
            $unit->del() ? $count++ : $failed++;
        }
        return response(['success' => !!$count, 'count' => $count, 'failed' => $failed]);
    }

    public function index()
    {
        return response()->json(Unit::with('baseUnit')->table(Unit::$searchable));
    }

    public function show(Unit $unit)
    {
        return $unit->load(['baseUnit', 'subunits']);
    }

    public function store(UnitRequest $request)
    {
        $unit = Unit::create($request->validated());
        return response(['success' => true, 'data' => $unit]);
    }

    public function update(UnitRequest $request, Unit $unit)
    {
        $updated = $unit->update($request->validated());
        return response(['success' => $updated, 'data' => $unit->refresh()]);
    }
}
