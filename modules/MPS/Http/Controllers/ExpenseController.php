<?php

namespace Modules\MPS\Http\Controllers;

use Illuminate\Http\Request;
use Modules\MPS\Models\Expense;
use Modules\MPS\Http\Requests\ExpenseRequest;

class ExpenseController extends Controller
{
    public function approve(Request $request, Expense $expense)
    {
        $user = $request->user();
        if (!$user->hasRole('super') && $expense->user_id != $user->id) {
            return response(['success' => false]);
        }
        return response(['success' => $expense->update(['approved' => 1])]);
    }

    public function create()
    {
        return extra_attributes('expense');
    }

    public function destroy(Expense $expense)
    {
        $expense->del();
        return response(['success' => true]);
    }

    public function destroyMany(Request $request)
    {
        $count = $failed = 0;
        foreach (Expense::whereIn('id', $request->ids)->get() as $expense) {
            $expense->del() ? $count++ : $failed++;
        }
        return response(['success' => !!$count, 'count' => $count, 'failed' => $failed]);
    }

    public function index(Request $request)
    {
        $expenses = Expense::query()->with([
            'account:id,name', 'location:id,name', 'categories:id,name', 'user:id,name', 'approvedBy:id,name',
        ]);
        if ($request->require_approval == 1) {
            $expenses->requireApproval();
        }
        return response()->json($expenses->table(Expense::$searchable));
    }

    public function show(Expense $expense)
    {
        $expense->attributes = extra_attributes('expense');
        return $expense->load([
            'account:id,name', 'location', 'categories:id,name', 'user:id,name', 'approvedBy:id,name',
        ]);
    }

    public function store(ExpenseRequest $request)
    {
        $data                = $request->validated();
        $data['date']        = $data['date'] ?? now();
        $data['approved']    = user()->hasRole('admin');
        $data['approved_by'] = $data['approved_by'] ?? user()->id;

        $expense = user()->expenses()->create($data);
        $expense->categories()->sync($data['category_id']);
        $expense->saveAttachments($request->file('attachments'));
        return response(['success' => !!$expense, 'data' => $expense]);
    }

    public function update(ExpenseRequest $request, Expense $expense)
    {
        $data   = $request->validated();
        $update = $expense->update($data);
        $expense->refresh()->categories()->sync($data['category_id']);
        $expense->saveAttachments($request->file('attachments'));
        return response(['success' => $update, 'data' => $expense->load('categories:id,name')]);
    }
}
