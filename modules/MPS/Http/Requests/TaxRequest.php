<?php

namespace Modules\MPS\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaxRequest extends FormRequest
{
    public function authorize()
    {
        return true; // TODO: only if admin
    }

    public function rules()
    {
        return [
            'same'        => 'boolean',
            'name'        => 'required',
            'state'       => 'boolean',
            'number'      => 'nullable',
            'compound'    => 'boolean',
            'details'     => 'nullable',
            'recoverable' => 'boolean',
            'rate'        => 'nullable|numeric',
            'code'        => 'bail|required|alpha_num|max:20|unique:taxes,code,' . $this->id,
        ];
    }
}
