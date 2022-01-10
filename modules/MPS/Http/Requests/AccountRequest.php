<?php

namespace Modules\MPS\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AccountRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'offline'         => 'nullable',
            'details'         => 'nullable',
            'type'            => 'required|max:55',
            'name'            => 'required|max:55',
            'reference'       => 'nullable|max:255',
            'opening_balance' => 'required|numeric',
            'fees'            => 'nullable|boolean',
            'fixed'           => 'nullable|numeric',
            'percentage'      => 'nullable|numeric',
            'apply_to'        => 'nullable|in:in,out,both',
        ];
    }
}
