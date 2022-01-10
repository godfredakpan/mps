<?php

namespace Modules\MPS\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecurringSaleRequest extends FormRequest
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
            'draft'                 => 'nullable',
            'repeat'                => 'required',
            'payment'               => 'nullable',
            'discount'              => 'nullable',
            'shipping'              => 'nullable',
            'customer_id'           => 'required',
            'create_before'         => 'nullable',
            'taxes'                 => 'nullable|array',
            'create_payment'        => 'nullable',
            'reference'             => 'nullable|max:50',
            'extra_attributes'      => 'nullable',
            'start_date'            => 'required|date',
            'items'                 => 'array|min:1|required',
            'items.*.id'            => 'nullable',
            'items.*.code'          => 'required',
            'items.*.name'          => 'required',
            'items.*.item_id'       => 'required',
            'items.*.discount'      => 'nullable',
            'items.*.batch_no'      => 'nullable',
            'items.*.taxes'         => 'nullable|array',
            'items.*.price'         => 'required|numeric',
            'items.*.comment'       => 'nullable|string',
            'items.*.allTaxes'      => 'nullable|array',
            'items.*.quantity'      => 'required|numeric',
            'items.*.promotions'    => 'nullable',
            'items.*.allPromotions' => 'nullable',
            'items.*.unit_id'       => 'nullable|exists:units,id',
            'items.*.expiry_date'   => 'nullable|date',
            'items.*.tax_included'  => 'nullable|Boolean',

            'attachments'   => 'nullable',
            'attachments.*' => 'mimes:' . env('ATTACHMENT_EXTS', 'jpg,png,pdf,zip'),

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
