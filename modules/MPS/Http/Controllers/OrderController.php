<?php

namespace Modules\MPS\Http\Controllers;

use Illuminate\Http\Request;
use Modules\MPS\Models\Order;
use Modules\MPS\Models\Location;

class OrderController extends Controller
{
    public function destroy(Order $order)
    {
        $order->delete();
        return response(['success' => true]);
    }

    public function destroyMany(Request $request)
    {
        $count = $failed = 0;
        foreach (Order::whereIn('id', $request->ids)->get() as $order) {
            $order->delete() ? $count++ : $failed++;
        }
        return response(['success' => !!$count, 'count' => $count, 'failed' => $failed]);
    }

    public function index()
    {
        return response()->json(Order::table(Order::$searchable));
    }

    public function search(Request $request)
    {
        return Order::where('location_id', session('location_id'))->get()->transform(function ($item) {
            $item->orderId = $item->id;
            unset($item->id);
            return $item;
        })->keyBy('oId');
    }

    public function show(Order $order)
    {
        $order->attributes = extra_attributes('sale');
        return $order;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'oId'              => 'required',
            'date'             => 'nullable',
            'reference'        => 'nullable',
            'discount'         => 'nullable',
            'discount_amount'  => 'nullable',
            'total_items'      => 'nullable',
            'total_quantity'   => 'nullable',
            'grand_total'      => 'nullable',
            'taxes'            => 'nullable|array',
            'items'            => 'nullable|array',
            'note'             => 'nullable',
            'user_id'          => 'nullable',
            'customer_id'      => 'nullable',
            'location_id'      => 'nullable',
            'extra_attributes' => 'nullable',
        ]);
        $register            = register_record();
        $data['location_id'] = $data['location_id'] ?? session('location_id');
        if ($register && $register->location_id != $data['location_id']) {
            $location = Location::find($data['location_id']);
            return response(['success' => false, 'message' => str_replace(['{x}', '{y}'], [$register->location->name, $location->name], __('register_opened_error'))], 422);
        } elseif (!$register) {
            return response(['success' => true, 'order' => []]);
        }
        $data['user_id']    = $data['user_id'] ?? (session()->has('impersonate') ? session()->get('impersonate') : auth()->id());
        $data['created_by'] = $request->user()->id;
        $order              = Order::where('oId', $data['oId'])->where('created_by', $data['created_by'])->first();
        if ($order) {
            $order->update($data);
        } else {
            $order = Order::create($data);
        }
        $order = Order::with(['user', 'customer'])->find($order->id);
        return response(['success' => $order->id, 'order' => $order]);
    }
}
