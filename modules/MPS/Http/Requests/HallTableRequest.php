<?php

namespace Modules\MPS\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class HallTableRequest extends FormRequest
{
    public function authorize()
    {
        return true; // TODO:
    }

    public function rules()
    {
        return [
            'code' => [
                'bail',
                'required',
                'alpha_dash',
                Rule::unique('hall_tables', 'code')->ignore($this->id)->where(function ($query) {
                    return $query->where('hall_id', $this->hall_id);
                }),
            ],
            'title'   => 'required',
            'details' => 'nullable',
            'hall_id' => 'bail|required|exists:halls,id',
        ];
    }
}
