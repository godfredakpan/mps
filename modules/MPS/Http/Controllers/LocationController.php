<?php

namespace Modules\MPS\Http\Controllers;

use Illuminate\Http\Request;
use Modules\MPS\Models\Location;
use Modules\MPS\Http\Requests\LocationRequest;

class LocationController extends Controller
{
    public function __construct()
    {
        cache()->forget('location');
    }

    public function create()
    {
        return extra_attributes('location');
    }

    public function destroy(Location $location)
    {
        return response(['success' => $location->del()]);
    }

    public function destroyMany(Request $request)
    {
        $count = $failed = 0;
        foreach (Location::whereIn('id', $request->ids)->get() as $location) {
            $location->del() ? $count++ : $failed++;
        }
        return response(['success' => !!$count, 'count' => $count, 'failed' => $failed]);
    }

    public function index()
    {
        return response()->json(Location::with('account')->table(Location::$searchable));
    }

    public function show(Location $location)
    {
        $location->attributes = extra_attributes('location');
        return $location->loadMissing(['registers']);
    }

    public function store(LocationRequest $request)
    {
        $data     = $request->validated();
        $location = Location::create($data);
        $location->accounts()->sync($data['accounts'] ?? []);
        $location->registers()->createMany($data['registers']);
        return response(['success' => true, 'data' => $location]);
    }

    public function update(LocationRequest $request, Location $location)
    {
        $data    = $request->validated();
        $updated = $location->update($data);
        $location->accounts()->sync($data['accounts'] ?? []);
        $location->syncHasMany($data['registers'], 'registers');
        return response(['success' => $updated, 'data' => $location->refresh()]);
    }
}
