<?php

namespace Modules\MPS\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
    // protected function passedValidation()
    // {
    //     if ($this->gateway == env('CARD_GATEWAY')) {
    //         if ($this->gateway == 'Stripe' && !$this->token_id) {
    //             throw ValidationException::withMessages(['payment' => __('stripe_token_error')]);
    //         }
    //     }
    // }

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'details'             => 'nullable',
            'gateway'          => 'nullable',
            'sale_id'          => 'nullable',
            'purchase_id'      => 'nullable',
            'received'         => 'nullable',
            'reference'        => 'nullable',
            'return_id'        => 'nullable',
            'card_holder'      => 'nullable',
            'account_id'       => 'required|exists:accounts,id',
            'amount'           => 'bail|required|numeric|min:0.01',
            'cheque_number'    => 'nullable|required_if:gateway,cheque',
            'token_id'         => 'nullable|required_if:gateway,Stripe',
            'gift_card_number' => 'nullable|required_if:gateway,gift_card',
            'customer_id'      => 'nullable|required_without:supplier_id|exists:customers,id',
            'supplier_id'      => 'nullable|required_without:customer_id|exists:suppliers,id',
            'firstName'        => 'nullable|required_if:gateway,PayPal_Pro|required_if:gateway,PayPal_Rest|required_if:gateway,AuthorizeNetApi_Api',
            'lastName'         => 'nullable|required_if:gateway,PayPal_Pro|required_if:gateway,PayPal_Rest|required_if:gateway,AuthorizeNetApi_Api',
            'billingAddress1'  => 'nullable|required_if:gateway,PayPal_Pro|required_if:gateway,PayPal_Rest|required_if:gateway,AuthorizeNetApi_Api',
            'billingCity'      => 'nullable|required_if:gateway,PayPal_Pro|required_if:gateway,PayPal_Rest|required_if:gateway,AuthorizeNetApi_Api',
            'billingPostcode'  => 'nullable|required_if:gateway,PayPal_Pro|required_if:gateway,PayPal_Rest|required_if:gateway,AuthorizeNetApi_Api',
            'billingState'     => 'nullable|required_if:gateway,PayPal_Pro|required_if:gateway,PayPal_Rest|required_if:gateway,AuthorizeNetApi_Api',
            'billingCountry'   => 'nullable|required_if:gateway,PayPal_Pro|required_if:gateway,PayPal_Rest|required_if:gateway,AuthorizeNetApi_Api',
            'cvv'              => 'nullable|required_if:gateway,PayPal_Pro|required_if:gateway,PayPal_Rest|required_if:gateway,AuthorizeNetApi_Api',
            'card_number'      => 'nullable|required_if:gateway,credit_card|required_if:gateway,PayPal_Pro|required_if:gateway,PayPal_Rest|required_if:gateway,AuthorizeNetApi_Api',
            'expiry_date'      => 'nullable|date_format:Y-m|required_if:gateway,credit_card|required_if:gateway,PayPal_Pro|required_if:gateway,PayPal_Rest|required_if:gateway,AuthorizeNetApi_Api',

            'attachments'   => 'nullable',
            'attachments.*' => 'mimes:' . env('ATTACHMENT_EXTS', 'jpg,png,pdf,zip'),
        ];
    }

    public function validated()
    {
        $data = $this->validator->validated();
        if ((isset($data['supplier_id']) && !empty($data['supplier_id'])) || (isset($data['gateway']) && ($data['gateway'] == 'cash' || $data['gateway'] == 'other'))) {
            $data['received'] = 1;
        }
        return $data;
    }
}
