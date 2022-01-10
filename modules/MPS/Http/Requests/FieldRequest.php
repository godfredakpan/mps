<?php

namespace Modules\MPS\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FieldRequest extends FormRequest
{
    public function authorize()
    {
        return true; // TODO: only if admin
    }

    public function rules()
    {
        return [
            'name'     => 'required|string',
            'type'     => 'required|string',
            'order'    => 'nullable|numeric',
            'entities' => 'required|array',
            'details'  => 'nullable|string',
            'options'  => 'nullable|string',
            'required' => 'nullable|boolean',
            'slug'     => 'bail|required|alpha_dash|unique:fields,slug,' . $this->id,
        ];
    }
}
