<?php

namespace Modules\MPS\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ModifierRequest extends FormRequest
{
    public function authorize()
    {
        return true; // TODO:
    }

    public function rules()
    {
        return [
            'code'               => 'bail|required|alpha_dash|unique:modifiers,code,' . $this->id,
            'title'              => 'required',
            'show_as'            => 'required|string',
            'details'            => 'nullable',
            'extra_attributes'   => 'nullable',
            'options'            => 'array|min:1',
            'options.*.item_id'  => 'required',
            'options.*.sku'      => 'required|alpha_dash',
            'options.*.quantity' => 'nullable|numeric',
            // 'options.*.name'     => 'nullable|string',
            // 'options.*.cost'     => 'nullable|numeric',
            // 'options.*.price'    => 'nullable|numeric',
        ];
    }
}
