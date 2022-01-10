<?php

namespace Modules\MPS\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRecordRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'comment'                    => 'nullable',
            'transferred_to'             => 'nullable',
            'cash_in_hand'               => 'required|numeric',
            'total_cash_amount'          => 'required|numeric',
            'total_cash_submitted'       => 'required|numeric',
            'total_cheques'              => 'required|numeric',
            'total_cheques_amount'       => 'required|numeric',
            'total_cheques_submitted'    => 'required|numeric',
            'total_cc_slips'             => 'required|numeric',
            'total_cc_slips_amount'      => 'required|numeric',
            'total_cc_slips_submitted'   => 'required|numeric',
            'total_other_amount'         => 'required|numeric',
            'total_refunds_amount'       => 'required|numeric',
            'total_expenses_amount'      => 'required|numeric',
            'total_gift_card_amount'     => 'required|numeric',
            'total_return_orders_amount' => 'required|numeric',
        ];
    }
}
