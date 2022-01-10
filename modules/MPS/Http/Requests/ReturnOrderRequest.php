<?php

namespace Modules\MPS\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReturnOrderRequest extends FormRequest
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
            'details'               => 'nullable',
            'discount'              => 'nullable',
            'shipping'              => 'nullable',
            'customer_id'           => 'required_if:type,sale',
            'supplier_id'           => 'required_if:type,purchase',
            'type'                  => 'required|in:sale,purchase',
            'create_payment'        => 'nullable',
            'extra_attributes'      => 'nullable',
            'deduct_from_register'  => 'nullable',
            'taxes'                 => 'nullable|array',
            'reference'             => 'nullable|max:50',
            'date'                  => 'required|date',
            'items'                 => 'array|min:1|required',
            'items.*.id'            => 'nullable',
            'items.*.code'          => 'required',
            'items.*.name'          => 'required',
            'items.*.item_id'       => 'required',
            'items.*.discount'      => 'nullable',
            'items.*.batch_no'      => 'nullable',
            'items.*.taxes'         => 'nullable|array',
            'items.*.allTaxes'      => 'nullable|array',
            'items.*.comment'       => 'nullable|string',
            'items.*.price'         => 'required|numeric',
            'items.*.quantity'      => 'required|numeric',
            'items.*.promotions'    => 'nullable|array',
            'items.*.allPromotions' => 'nullable|array',
            'items.*.unit_id'       => 'nullable|exists:units,id',
            'items.*.expiry_date'   => 'nullable|date',
            'items.*.tax_included'  => 'nullable|Boolean',

            'attachments'   => 'nullable',
            'attachments.*' => 'mimes:' . env('ATTACHMENT_EXTS', 'jpg,png,pdf,zip'),

            'add_payment' => 'nullable',
            // 'payment.type' => 'required|string',
            // 'payment.amount' => 'required|numeric',
            // 'payment.paying_by' => 'required|string',
            // 'payment.payment_details' => 'required|string',
        ];
    }

    public function validated()
    {
        $data = $this->validator->validated();
        if ($this->has('attachment')) {
            $path               = $this->attachment->store('/attachments', 'public');
            $data['attachment'] = \Storage::disk('public')->url($path);
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
