<?php

namespace Modules\MPS\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PromotionRequest extends FormRequest
{
    public function authorize()
    {
        return true; // TODO:
    }

    public function rules()
    {
        return [
            'name'            => 'bail|required|unique:promotions,name,' . $this->id,
            'type'            => 'bail|required|in:simple,advance,BXGY,SXGD',
            'start_date'      => 'nullable|date',
            'end_date'        => 'nullable|date',
            'active'          => 'nullable',
            'discount'        => 'nullable|numeric',
            'discount_method' => 'nullable|in:fixed,percentage',
            'show_on_receipt' => 'nullable',
            'details'         => 'nullable|string',
            'amount_to_spend' => 'nullable|numeric',
            'item_id_to_buy'  => 'nullable',
            'quantity_to_buy' => 'nullable|numeric',
            'item_id_to_get'  => 'nullable',
            'quantity_to_get' => 'nullable|numeric',
            'times_to_apply'  => 'nullable',
            'coupon_code'     => 'nullable',
            'items'           => 'nullable|array',
            'categories'      => 'nullable|array',
        ];
    }
}
