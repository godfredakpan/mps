<?php

namespace Modules\MPS\Http\Controllers;

use Illuminate\Http\Request;
use Modules\MPS\Models\Modifier;
use Modules\MPS\Http\Requests\ModifierRequest;

class ModifierController extends Controller
{
    public function create()
    {
        return extra_attributes('modifier');
    }

    public function destroy(Modifier $modifier)
    {
        $modifier->del();
        return response(['success' => true]);
    }

    public function destroyMany(Request $request)
    {
        $count = $failed = 0;
        foreach (Modifier::whereIn('id', $request->ids)->get() as $modifier) {
            $modifier->del() ? $count++ : $failed++;
        }
        return response(['success' => !!$count, 'count' => $count, 'failed' => $failed]);
    }

    public function index()
    {
        return response()->json(Modifier::table(Modifier::$searchable));
    }

    public function show(Modifier $modifier)
    {
        $modifier->attributes = extra_attributes('modifier');
        return $modifier;
    }

    public function store(ModifierRequest $request)
    {
        $data     = $request->validated();
        $modifier = Modifier::create($data);
        $modifier->syncHasMany($data['options'], 'options');
        return response(['success' => !!$modifier, 'data' => $modifier]);
    }

    public function update(ModifierRequest $request, Modifier $modifier)
    {
        $data    = $request->validated();
        $updated = $modifier->update($data);
        $modifier->syncHasMany($data['options'], 'options', 'sku');
        return response(['success' => $updated, 'data' => $modifier->refresh()]);
    }
}
