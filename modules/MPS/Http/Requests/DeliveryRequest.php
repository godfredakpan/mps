<?php

namespace Modules\MPS\Http\Requests;

use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Http\FormRequest;

class DeliveryRequest extends FormRequest
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
            'driver'               => 'nullable',
            'status'               => 'required',
            'extra_attributes'     => 'nullable',
            'details'              => 'nullable',
            'reference'            => 'nullable|max:50',
            'date'                 => 'required|date',
            'customer_id'          => 'required|exists:customers,id',
            'delivered_at'         => 'required_if:status,delivered',
            'delivered_by'         => 'required_if:status,delivered',
            'received_by'          => 'required_if:status,delivered',
            'items'                => 'array|min:1|required',
            'items.*.id'           => 'nullable',
            'items.*.code'         => 'required',
            'items.*.name'         => 'required',
            'items.*.batch_no'     => 'nullable',
            'items.*.selected'     => 'nullable',
            'items.*.comment'      => 'nullable|string',
            'items.*.quantity'     => 'required|numeric',
            'items.*.unit_id'      => 'nullable|exists:units,id',
            'items.*.item_id'      => 'nullable|exists:items,id',
            'items.*.expiry_date'  => 'nullable|date',
            'items.*.sale_item_id' => 'nullable|exists:sale_items,id',

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
