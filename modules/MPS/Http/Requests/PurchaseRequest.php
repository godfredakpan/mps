<?php

namespace Modules\MPS\Http\Requests;

use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
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
            'details'              => 'nullable',
            'type'                 => 'required',
            'draft'                => 'nullable',
            'discount'             => 'nullable',
            'shipping'             => 'nullable',
            'supplier_id'          => 'required',
            'create_payment'       => 'nullable',
            'extra_attributes'     => 'nullable',
            'taxes'                => 'nullable|array',
            'reference'            => 'nullable|max:50',
            'date'                 => 'required|date',
            'items'                => 'array|min:1|required',
            'items.*.id'           => 'nullable',
            'items.*.code'         => 'required',
            'items.*.name'         => 'required',
            'items.*.item_id'      => 'required',
            'items.*.discount'     => 'nullable',
            'items.*.batch_no'     => 'nullable',
            'items.*.allTaxes'     => 'nullable|array',
            'items.*.taxes'        => 'nullable|array',
            'items.*.comment'      => 'nullable|string',
            'items.*.cost'         => 'required|numeric',
            'items.*.quantity'     => 'required|numeric',
            'items.*.unit_id'      => 'nullable|exists:units,id',
            'items.*.expiry_date'  => 'nullable|date',
            'items.*.item_unit_id' => 'required_with:items.*.unit_id|exists:units,id',
            'items.*.tax_included' => 'nullable|Boolean',
            'add_payment'          => 'nullable',

            'attachments'   => 'nullable',
            'attachments.*' => 'mimes:' . env('ATTACHMENT_EXTS', 'jpg,png,pdf,zip'),

            'items.*.selected.*.serials' => 'required_if:items.*.has_serials,1',
            // 'items.*.selected.serials.*.number' => 'required_if:items.*.has_serials,1',
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
                    return 'item ' . ((int) $attributes[1] + 1) . ' ' . (isset($attributes[3]) && $attributes[3] == 'serials' ? $attributes[3] : ($relations[0] . ' ' . (isset($relations[2]) ? $relations[1] : '')));
                }
                return 'item ' . ((int) $attributes[1] + 1);
            } elseif ($attributes[0] == 'attachments') {
                return 'attachments ' . ((int) $attributes[1] + 1);
            }
            return $attributes;
        });
    }
}
