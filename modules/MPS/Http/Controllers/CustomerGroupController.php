<?php

namespace Modules\MPS\Http\Controllers;

use Illuminate\Http\Request;
use Modules\MPS\Models\CustomerGroup;
use Modules\MPS\Http\Requests\CustomerGroupRequest;

class CustomerGroupController extends Controller
{
    public function destroy(CustomerGroup $customer_group)
    {
        return response(['success' => $customer_group->del()]);
    }

    public function destroyMany(Request $request)
    {
        $count = $failed = 0;
        foreach (CustomerGroup::whereIn('id', $request->ids)->get() as $customer_group) {
            $customer_group->del() ? $count++ : $failed++;
        }
        return response(['success' => !!$count, 'count' => $count, 'failed' => $failed]);
    }

    public function index()
    {
        return response()->json(CustomerGroup::table(CustomerGroup::$searchable));
    }

    public function show(CustomerGroup $customer_group)
    {
        $customer_group->attributes = extra_attributes('customer_group');
        return $customer_group;
    }

    public function store(CustomerGroupRequest $request)
    {
        $customer_group = CustomerGroup::create($request->validated());
        return response(['success' => !!$customer_group, 'data' => $customer_group]);
    }

    public function update(CustomerGroupRequest $request, CustomerGroup $customer_group)
    {
        $updated = $customer_group->update($request->validated());
        return response(['success' => $updated, 'data' => $customer_group->refresh()]);
    }
}
