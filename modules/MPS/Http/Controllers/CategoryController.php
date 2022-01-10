<?php

namespace Modules\MPS\Http\Controllers;

use Illuminate\Http\Request;
use Modules\MPS\Models\Category;
use Modules\MPS\Http\Requests\CategoryRequest;

class CategoryController extends Controller
{
    public function __construct()
    {
        cache()->forget('categories');
    }

    public function destroy(Category $category)
    {
        $category->del();
        return response(['success' => true]);
    }

    public function destroyMany(Request $request)
    {
        $count = $failed = 0;
        foreach (Category::whereIn('id', $request->ids)->get() as $category) {
            $category->del() ? $count++ : $failed++;
        }
        return response(['success' => !!$count, 'count' => $count, 'failed' => $failed]);
    }

    public function index(Request $request)
    {
        if ($request->all == 'yes') {
            return response()->json(Category::with('parent')->get());
        }
        return response()->json(Category::with('parent')->table(Category::$searchable));
    }

    public function show(Category $category)
    {
        return $category;
    }

    public function store(CategoryRequest $request)
    {
        $category = Category::create($request->validated());
        return response(['success' => true, 'data' => $category]);
    }

    public function update(CategoryRequest $request, Category $category)
    {
        $updated = $category->update($request->validated());
        return response(['success' => $updated, 'data' => $category->refresh()]);
    }
}
