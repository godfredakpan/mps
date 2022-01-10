<?php

namespace Modules\MPS\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IncomeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'date'             => 'nullable',
            'details'          => 'nullable',
            'extra_attributes' => 'nullable',
            'title'            => 'required|max:55',
            'amount'           => 'required|numeric',
            'reference'        => 'nullable|max:255',
            'account_id'       => 'bail|required|exists:accounts,id',
            'category_id'      => 'bail|required|exists:categories,id',
            // 'taxes'          => 'nullable|array',

            'attachments'   => 'nullable',
            'attachments.*' => 'mimes:' . env('ATTACHMENT_EXTS', 'jpg,png,pdf,zip'),
        ];
    }
}
