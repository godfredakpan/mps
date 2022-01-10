<?php

namespace Modules\MPS\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerGroupRequest extends FormRequest
{
    public function authorize()
    {
        return true; // TODO:
    }

    public function rules()
    {
        return [
            'name'     => 'required',
            'discount' => 'required|numeric',
            'code'     => 'bail|required|unique:customer_groups,code,' . $this->id,
        ];
    }
}
