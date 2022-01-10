<?php

namespace Modules\MPS\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UnitRequest extends FormRequest
{
    public function authorize()
    {
        return true; // TODO: only if admin
    }

    public function rules()
    {
        return [
            'name'            => 'required',
            'details'         => 'nullable',
            'operator'        => 'nullable|in:+,-,*,/',
            'operation_value' => 'nullable|numeric',
            'base_id'         => 'nullable|exists:units,id',
            'code'            => 'bail|required|alpha_num|max:20|unique:units,code,' . $this->id,
        ];
    }
}
