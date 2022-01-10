<?php

namespace Modules\MPS\Http\Controllers;

use Modules\MPS\Models\RegisterRecord;
use Modules\MPS\Events\RegisterRecordEvent;
use Modules\MPS\Http\Requests\RegisterRecordRequest;

class RegisterRecordController extends Controller
{
    public function close(RegisterRecordRequest $request, RegisterRecord $register_record)
    {
        $form = $request->validated();

        $form['closed_at'] = now();
        $form['closed_by'] = auth()->id();
        $register_record->fill($form)->save();
        event(new RegisterRecordEvent($register_record, user()));
        return response(['success' => true]);
    }

    public function index()
    {
        return response()->json(RegisterRecord::with([
            'register:id,name', 'user:id,name',
            'closedByUser:id,name', 'location:id,name',
        ])->table(RegisterRecord::$searchable));
    }

    public function show(RegisterRecord $register_record)
    {
        $user                      = $register_record->user;
        $register_record->payments = $register_record->payments()->select(['amount', 'gateway', 'received'])->get();

        $register_record->total_cash_amount          = $register_record->payments->where('received', 1)->where('gateway', 'cash')->sum('amount');
        $register_record->total_cc_slips             = $register_record->payments->where('received', 1)->where('gateway', 'credit_card')->count();
        $register_record->total_cc_slips_amount      = $register_record->payments->where('received', 1)->where('gateway', 'credit_card')->sum('amount');
        $register_record->total_cheques              = $register_record->payments->where('received', 1)->where('gateway', 'cheque')->count();
        $register_record->total_cheques_amount       = $register_record->payments->where('received', 1)->where('gateway', 'cheque')->sum('amount');
        $register_record->total_other_amount         = $register_record->payments->where('received', 1)->where('gateway', 'other')->sum('amount');
        $register_record->total_gift_card_amount     = $register_record->payments->where('received', 1)->where('gateway', 'gift_card')->sum('amount');
        $register_record->total_sales_amount         = $register_record->sales()->sum('grand_total');
        $register_record->total_expenses_amount      = $user->expenses()->where('created_at', $register_record->created_at)->where('date', $register_record->created_at->format('Y-m-d'))->sum('amount');
        $register_record->total_return_orders_amount = $user->returnOrders()->where('created_at', $register_record->created_at)->where('date', $register_record->created_at->format('Y-m-d'))->sum('grand_total');
        $register_record->total_refunds_amount       = $user->returnOrders()->where('deduct_from_register', 1)->where('created_at', $register_record->created_at)->where('date', $register_record->created_at->format('Y-m-d'))->sum('grand_total');
        unset($register_record->payments);
        return $register_record->load(['register:id,name', 'user:id,name']);
    }
}
