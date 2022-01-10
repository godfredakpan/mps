<?php

namespace Modules\MPS\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HallRequest extends FormRequest
{
    public function authorize()
    {
        return true; // TODO:
    }

    public function rules()
    {
        return [
            'title'       => 'required',
            'details'     => 'nullable',
            'location_id' => 'bail|required|exists:locations,id',
            'code'        => 'bail|required|alpha_dash|unique:halls,code,' . $this->id,
        ];
    }
}
