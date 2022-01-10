<?php

namespace Modules\MPS\Http\Controllers;

use Illuminate\Http\Request;
use Modules\MPS\Models\Field;
use Modules\MPS\Http\Requests\FieldRequest;

class FieldController extends Controller
{
    public function destroy(Field $field)
    {
        return response(['success' => $field->del()]);
    }

    public function destroyMany(Request $request)
    {
        $count = $failed = 0;
        foreach (Field::whereIn('id', $request->ids)->get() as $field) {
            $field->del() ? $count++ : $failed++;
        }
        return response(['success' => !!$count, 'count' => $count, 'failed' => $failed]);
    }

    public function index()
    {
        return response()->json(Field::table(Field::$searchable));
    }

    public function show(Field $field)
    {
        return $field;
    }

    public function store(FieldRequest $request)
    {
        $field = Field::create($request->validated());
        return response(['success' => true, 'data' => $field]);
    }

    public function update(FieldRequest $request, Field $field)
    {
        $field->update($request->validated());
        return response(['success' => true, 'data' => $field->refresh()]);
    }
}
