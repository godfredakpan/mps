<?php

namespace Modules\MPS\Http\Controllers;

use Illuminate\Http\Request;
use Modules\MPS\Models\Customer;
use Modules\MPS\Http\Requests\CustomerRequest;

class PosController extends Controller
{
    public function addCustomer(CustomerRequest $request)
    {
        $customer = Customer::create($request->validated());
        return response(['success' => true, 'customer' => $customer->load('customerGroup')]);
    }

    public function index(Request $request)
    {
        $items = [];
        if ($cId = $request->category) {
            $items = \Modules\MPS\Models\Item::pos()->withAll()
                ->whereHas('categories', function ($query) use ($cId) {
                    $query->where('id', $cId);
                })->orderBy('name')->get();
        }

        $user_id = (session()->has('impersonate') ? session()->get('impersonate') : auth()->id());
        $orders  = \Modules\MPS\Models\Order::where('location_id', session('location_id'))->where(
            fn ($q) => $q->where('created_by', auth()->id())->orWhere('user_id', $user_id)
        )->get();
        if ($orders->isNotEmpty()) {
            $orders = $orders->transform(function ($item) {
                $item->orderId = $item->id;
                unset($item->id);
                return $item;
            })->keyBy('oId');
        }

        $cIds[] = mps_config('default_customer');
        foreach ($orders as $order) {
            if (!empty($order->customer_id) && !in_array($order->customer_id, $cIds)) {
                $cIds[] = $order->customer_id;
            }
        }

        $customers = transform_select(
            \Modules\MPS\Models\Customer::whereIn('id', $cIds)->with('customerGroup')->get(),
            ['id' => 'value', 'name' => 'label', 'state', 'country', 'customer_group']
        );

        return [
            'items'      => $items,
            'orders'     => $orders,
            'customers'  => $customers,
            'register'   => register_record(),
            'attributes' => extra_attributes('sale'),
            'taxes'      => \Modules\MPS\Models\Tax::all(),
            'categories' => \Modules\MPS\Models\Category::parents()->with('children')->get(),
            'location'   => \Modules\MPS\Models\Location::with('registers')->find(session('location_id')),
            'halls'      => \Modules\MPS\Models\Hall::where('location_id', session('location_id'))->orderBy('code')->with('tables')->get(),
        ];
    }
}
