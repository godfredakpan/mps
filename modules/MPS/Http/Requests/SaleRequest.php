<?php

namespace Modules\MPS\Http\Requests;

use Modules\MPS\Models\GiftCard;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class SaleRequest extends FormRequest
{
    protected function passedValidation()
    {
        $gateway         = $this->input('payment.gateway');
        $git_card_number = $this->input('payment.gift_card_number');
        if ($gateway == 'gift_card' && $git_card_number) {
            $gift_card = GiftCard::where('number', $git_card_number)->first();
            if ($gift_card->balance < $this->input('payment.amount')) {
                throw ValidationException::withMessages(['payment' => __('gift_card_payment_error')]);
            }
        }
    }

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
            'type'                  => 'required',
            'draft'                 => 'nullable',
            'orderId'               => 'nullable',
            'discount'              => 'nullable',
            'shipping'              => 'nullable',
            'create_payment'        => 'nullable',
            'extra_attributes'      => 'nullable',
            'taxes'                 => 'nullable|array',
            'reference'             => 'nullable|max:50',
            'date'                  => 'required|date',
            'customer_id'           => 'required|exists:customers,id',
            'oId'                   => 'nullable',
            'pos'                   => 'nullable|boolean',
            'shop'                  => 'nullable|boolean',
            'register_id'           => 'nullable|required_if:pos,1|exists:registers,id',
            'register_record_id'    => 'nullable|required_if:pos,1|exists:register_records,id',
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
