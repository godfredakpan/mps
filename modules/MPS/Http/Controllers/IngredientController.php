<?php

namespace Modules\MPS\Http\Controllers;

use Illuminate\Http\Request;
use Modules\MPS\Models\Ingredient;
use Modules\MPS\Http\Requests\IngredientRequest;

class IngredientController extends Controller
{
    public function create()
    {
        return extra_attributes('ingredient');
    }

    public function destroy(Ingredient $ingredient)
    {
        $ingredient->del();
        return response(['success' => true]);
    }

    public function destroyMany(Request $request)
    {
        $count = $failed = 0;
        foreach (Ingredient::whereIn('id', $request->ids)->get() as $ingredient) {
            $ingredient->del() ? $count++ : $failed++;
        }
        return response(['success' => !!$count, 'count' => $count, 'failed' => $failed]);
    }

    public function index()
    {
        return response()->json(Ingredient::table(Ingredient::$searchable));
    }

    public function show(Ingredient $ingredient)
    {
        $ingredient->attributes = extra_attributes('ingredient');
        return $ingredient;
    }

    public function store(IngredientRequest $request)
    {
        $ingredient = Ingredient::create($request->validated());
        return response(['success' => !!$ingredient, 'data' => $ingredient]);
    }

    public function update(IngredientRequest $request, Ingredient $ingredient)
    {
        $updated = $ingredient->update($request->validated());
        return response(['success' => $updated, 'data' => $ingredient->refresh()]);
    }
}
