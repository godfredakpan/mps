<?php

namespace Modules\MPS\Http\Controllers;

use Modules\MPS\Models\Tax;
use Illuminate\Http\Request;
use Modules\MPS\Http\Requests\TaxRequest;

class TaxController extends Controller
{
    public function destroy(Tax $tax)
    {
        return response(['success' => $tax->del()]);
    }

    public function destroyMany(Request $request)
    {
        $count = $failed = 0;
        foreach (Tax::whereIn('id', $request->ids)->get() as $tax) {
            $tax->del() ? $count++ : $failed++;
        }
        return response(['success' => !!$count, 'count' => $count, 'failed' => $failed]);
    }

    public function index()
    {
        return response()->json(Tax::table(Tax::$searchable));
    }

    public function show(Tax $tax)
    {
        return $tax;
    }

    public function store(TaxRequest $request)
    {
        $tax = Tax::create($request->validated());
        return response(['success' => true, 'data' => $tax]);
    }

    public function update(TaxRequest $request, Tax $tax)
    {
        $tax->update($request->validated());
        return response(['success' => true, 'data' => $tax->refresh()]);
    }
}
