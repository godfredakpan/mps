<?php

namespace Modules\MPS\Http\Controllers;

use Illuminate\Http\Request;
use Modules\MPS\Models\Income;
use Modules\MPS\Http\Requests\IncomeRequest;

class IncomeController extends Controller
{
    public function create()
    {
        return extra_attributes('income');
    }

    public function destroy(Income $income)
    {
        $income->del();
        return response(['success' => true]);
    }

    public function destroyMany(Request $request)
    {
        $count = $failed = 0;
        foreach (Income::whereIn('id', $request->ids)->get() as $income) {
            $income->del() ? $count++ : $failed++;
        }
        return response(['success' => !!$count, 'count' => $count, 'failed' => $failed]);
    }

    public function index()
    {
        return response()->json(Income::with([
            'account:id,name', 'location:id,name', 'categories:id,name',
        ])->table(Income::$searchable));
    }

    public function show(Income $income)
    {
        $income->attributes = extra_attributes('income');
        return $income->load([
            'account:id,name', 'location', 'categories:id,name', 'user:id,name',
        ]);
    }

    public function store(IncomeRequest $request)
    {
        $data         = $request->validated();
        $data['date'] = $data['date'] ?? now();
        $income       = user()->incomes()->create($data);
        $income->categories()->sync($data['category_id']);
        $income->saveAttachments($request->file('attachments'));
        return response(['success' => !!$income, 'data' => $income]);
    }

    public function update(IncomeRequest $request, Income $income)
    {
        $data   = $request->validated();
        $update = $income->update($data);
        $income->refresh()->categories()->sync($data['category_id']);
        $income->saveAttachments($request->file('attachments'));
        return response(['success' => $update, 'data' => $income->load('categories:id,name')]);
    }
}
