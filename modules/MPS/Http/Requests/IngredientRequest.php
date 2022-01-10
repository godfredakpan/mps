<?php

namespace Modules\MPS\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IngredientRequest extends FormRequest
{
    public function authorize()
    {
        return true; // TODO:
    }

    public function rules()
    {
        return [
            'name'             => 'required',
            'details'          => 'nullable',
            'extra_attributes' => 'nullable',
            'cost'             => 'nullable|numeric',
            'price'            => 'nullable|numeric',
            'quantity'         => 'nullable|numeric',
            'code'             => 'bail|required|alpha_dash|unique:ingredients,code,' . $this->id,
            'sku'              => 'bail|required|alpha_dash|unique:ingredients,sku,' . $this->id,
        ];
    }
}
