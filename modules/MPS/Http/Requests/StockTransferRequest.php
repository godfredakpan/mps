<?php

namespace Modules\MPS\Http\Requests;

use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Http\FormRequest;

class StockTransferRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function messages()
    {
        return [
            'items.required' => __('items_required'),
        ];
    }

    public function rules()
    {
        return [
            'details'   => 'nullable',
            'reference' => 'nullable|max:250',
            'date'      => 'required|date',
            'from'      => 'required|different:to|exists:locations,id',
            'to'        => 'required|different:from|exists:locations,id',
            'status'    => 'required|in:pending,transferring,transferred',

            'items'                => 'array|min:1',
            'items.*.id'           => 'nullable',
            'items.*.sale_item_id' => 'nullable',
            'items.*.item_id'      => 'required',
            'items.*.code'         => 'required',
            'items.*.name'         => 'required',
            'items.*.comment'      => 'nullable|string',
            'items.*.quantity'     => 'required|numeric',

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
            if ($attributes[0] == 'items') {
                if ($attributes[2]) {
                    $relations = explode('_', $attributes[2]);
                    return 'item ' . ((int) $attributes[1] + 1) . ' ' . $relations[0] . ' ' . (isset($relations[2]) ? $relations[1] : '');
                }
                return 'item ' . ((int) $attributes[1] + 1);
            } elseif ($attributes[0] == 'attachments') {
                return 'attachments ' . ((int) $attributes[1] + 1);
            }
            return $attributes;
        });
    }
}
