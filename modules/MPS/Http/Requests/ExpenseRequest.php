<?php

namespace Modules\MPS\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExpenseRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'details'          => 'nullable',
            'extra_attributes' => 'nullable',
            'title'            => 'required|max:55',
            'amount'           => 'required|numeric',
            'reference'        => 'nullable|max:255',
            'approved_by_id'   => 'nullable|exists:users,id',
            'account_id'       => 'required|exists:accounts,id',
            'category_id'      => 'required|exists:categories,id',
            // 'approved'       => 'nullable',
            // 'taxes'          => 'nullable|array',

            'attachments'   => 'nullable',
            'attachments.*' => 'mimes:' . env('ATTACHMENT_EXTS', 'jpg,png,pdf,zip'),

            'recurring'       => 'nullable',
            'expense_id'      => 'nullable',
            'start_date'      => 'nullable',
            'repeat'          => 'nullable',
            'create_before'   => 'nullable',
            'last_created_at' => 'nullable',
        ];
    }
}
