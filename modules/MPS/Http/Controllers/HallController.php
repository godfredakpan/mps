<?php

namespace Modules\MPS\Http\Controllers;

use Illuminate\Http\Request;
use Modules\MPS\Models\Hall;
use Modules\MPS\Http\Requests\HallRequest;

class HallController extends Controller
{
    public function create()
    {
        return extra_attributes('hall');
    }

    public function destroy(Hall $hall)
    {
        $hall->del();
        return response(['success' => true]);
    }

    public function destroyMany(Request $request)
    {
        $count = $failed = 0;
        foreach (Hall::whereIn('id', $request->ids)->get() as $hall) {
            $hall->del() ? $count++ : $failed++;
        }
        return response(['success' => !!$count, 'count' => $count, 'failed' => $failed]);
    }

    public function index()
    {
        return response()->json(Hall::with(['location:id,name'])->table(Hall::$searchable));
    }

    public function show(Hall $hall)
    {
        $hall->attributes = extra_attributes('hall');
        return $hall->load('location:id,name');
    }

    public function store(HallRequest $request)
    {
        $hall = Hall::create($request->validated());
        return response(['success' => !!$hall, 'data' => $hall]);
    }

    public function update(HallRequest $request, Hall $hall)
    {
        $updated = $hall->update($request->validated());
        return response(['success' => $updated, 'data' => $hall->refresh()->load('location:id,name')]);
    }
}
