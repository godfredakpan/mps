<?php

namespace Modules\MPS\Http\Controllers;

use Illuminate\Http\Request;
use Modules\MPS\Models\User;
use Modules\MPS\Models\Customer;
use Modules\Shop\Models\Address;
use Modules\MPS\Http\Requests\AddressRequest;
use Modules\MPS\Http\Requests\CustomerRequest;
use Modules\MPS\Http\Requests\CustomerUserRequest;

class CustomerController extends Controller
{
    public function addAddress(AddressRequest $request)
    {
        $address = Address::create($request->validated());
        return response()->json(['success' => true, 'address' => $address]);
    }

    public function addresses($customer_id)
    {
        return Address::ofCustomer($customer_id)->get();
    }

    public function addUser(CustomerUserRequest $request)
    {
        $user = User::create($request->validated());
        $user->assignRole('customer');
        return response()->json(['success' => true, 'user' => $user]);
    }

    public function create()
    {
        return extra_attributes('customer');
    }

    public function destroy(Customer $customer)
    {
        return response(['success' => $customer->del()]);
    }

    public function destroyMany(Request $request)
    {
        $count = $failed = 0;
        foreach (Customer::whereIn('id', $request->ids)->get() as $customer) {
            $customer->del() ? $count++ : $failed++;
        }
        return response(['success' => !!$count, 'count' => $count, 'failed' => $failed]);
    }

    public function index(Request $request)
    {
        if ($request->due_limit) {
            return response()->json(Customer::with('journal')->whereHas(
                'journal',
                fn ($q) => $q->whereRaw('balance >= (customers.due_limit * (?/100))', $request->only('due_limit'))
            )->table(Customer::$searchable));
        }
        return response()->json(Customer::with('journal')->table(Customer::$searchable));
    }

    public function show(Customer $customer)
    {
        $customer->attributes = extra_attributes('customer');
        return $customer;
    }

    public function store(CustomerRequest $request)
    {
        $customer = Customer::create($request->validated());
        return response(['success' => !!$customer, 'data' => $customer]);
    }

    public function update(CustomerRequest $request, Customer $customer)
    {
        $updated = $customer->update($request->validated());
        return response(['success' => $updated, 'data' => $customer->refresh()]);
    }

    public function updateAddress(AddressRequest $request, Address $address)
    {
        $address->update($request->validated());
        return response()->json(['success' => true, 'address' => $address->refresh()]);
    }

    public function updateUser(CustomerUserRequest $request, User $user)
    {
        $user->update($request->validated());
        return response()->json(['success' => true, 'user' => $user->refresh()]);
    }

    public function users($customer_id)
    {
        return User::ofCustomer($customer_id)->get();
    }
}
