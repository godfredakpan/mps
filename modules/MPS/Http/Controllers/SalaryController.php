<?php

namespace Modules\MPS\Http\Controllers;

use Illuminate\Http\Request;
use Modules\MPS\Models\Salary;
use Illuminate\Support\Facades\Artisan;
use Modules\MPS\Http\Requests\SalaryRequest;

class SalaryController extends Controller
{
    public function destroy(Salary $salary)
    {
        return response(['success' => $salary->delete()]);
    }

    public function generate()
    {
        Artisan::call('staff:salaries');
        return response()->json(['success' => true, 'message' => Artisan::output()]);
    }

    public function index(Request $request)
    {
        $salaries = Salary::query()->with(['account:id,name', 'user:id,name']);
        if ($request->user_id) {
            $salaries->where('user_id', $request->input('user_id'));
        }
        return response()->json($salaries->table(Salary::$searchable));
    }

    public function show(Salary $salary)
    {
        return $salary->load(['account', 'user.location']);
    }

    public function store(SalaryRequest $request)
    {
        $form   = $request->validated();
        $salary = Salary::create($form);
        $salary->saveAttachments($request->file('attachments'));
        return response(['success' => true, 'data' => $salary]);
    }

    public function update(SalaryRequest $request, Salary $salary)
    {
        $form   = $request->validated();
        $update = $salary->update($form);
        $salary->saveAttachments($request->file('attachments'));
        return response(['success' => $update, 'data' => $salary->refresh()]);
    }
}
