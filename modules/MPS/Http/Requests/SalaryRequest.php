<?php

namespace Modules\MPS\Http\Requests;

use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class SalaryRequest extends FormRequest
{
    protected function passedValidation()
    {
        $salary = $this->route('salary');
        $points = (usermeta($this->user_id, 'points') ?: 0) + ($salary && $salary->points ? $salary->points : 0);
        if ($this->points > $points) {
            throw ValidationException::withMessages(['points' => __choice('user_x_points', ['points' => $points])]);
        }
    }

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'details'           => 'nullable',
            'advance'           => 'nullable|boolean',
            'reference'         => 'nullable|max:250',
            'amount'            => 'required|numeric',
            'work_hours'        => 'nullable|numeric',
            'work_hours_salary' => 'nullable|numeric',
            'status'            => 'nullable|in:paid,due',
            'date'              => 'required|date',
            'user_id'           => 'required|exists:users,id',
            'account_id'        => 'required|exists:accounts,id',
            'type'              => 'nullable|in:salary,commission',
            'points'            => 'required_if:type,commission',

            'attachments'   => 'nullable',
            'attachments.*' => 'mimes:' . env('ATTACHMENT_EXTS', 'jpg,png,pdf,zip'),
        ];
    }

    public function validated()
    {
        $data = $this->validator->validated();
        if ($this->has('attachment')) {
            $path               = $this->attachment->store('/attachments', 'public');
            $data['attachment'] = Storage::disk('public')->url($path);
        }
        return $data;
    }

    public function withValidator($validator)
    {
        $validator->setImplicitAttributesFormatter(function ($attribute) {
            $attributes = explode('.', $attribute);
            if ($attributes[0] == 'attachments') {
                return 'attachments ' . ((int) $attributes[1] + 1);
            }
            return $attributes;
        });
    }
}
