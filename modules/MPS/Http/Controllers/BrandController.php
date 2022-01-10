<?php

namespace Modules\MPS\Http\Controllers;

use Illuminate\Http\Request;
use Modules\MPS\Models\Brand;
use Modules\MPS\Http\Requests\BrandRequest;

class BrandController extends Controller
{
    public function __construct()
    {
        cache()->forget('brands');
    }

    public function destroy(Brand $brand)
    {
        return response(['success' => $brand->del()]);
    }

    public function destroyMany(Request $request)
    {
        $count = $failed = 0;
        foreach (Brand::whereIn('id', $request->ids)->get() as $brand) {
            $brand->del() ? $count++ : $failed++;
        }
        return response(['success' => !!$count, 'count' => $count, 'failed' => $failed]);
    }

    public function index()
    {
        return response()->json(Brand::table(Brand::$searchable));
    }

    public function show(Brand $brand)
    {
        return $brand;
    }

    public function store(BrandRequest $request)
    {
        $brand = Brand::create($request->validated());
        return response(['success' => true, 'data' => $brand]);
    }

    public function update(BrandRequest $request, Brand $brand)
    {
        $updated = $brand->update($request->validated());
        return response(['success' => $updated, 'data' => $brand->refresh()]);
    }
}
