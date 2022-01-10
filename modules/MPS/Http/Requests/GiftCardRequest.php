<?php

namespace Modules\MPS\Http\Requests;

use Modules\MPS\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class GiftCardRequest extends FormRequest
{
    protected function passedValidation()
    {
        $points   = $this->input('points');
        $card     = $this->route('gift_card');
        $customer = Customer::find($this->input('customer_id'));
        if ($points && $card) {
            $card->points ??= 0;
            if ($card->points && $card->customer_id && $customer && $card->customer_id == $customer->id && ($card->customer->points + $card->points) < $points) {
                throw ValidationException::withMessages(['points' => __('low_points_error')]);
            } elseif (!$customer || ($points && $customer->points < $points)) {
                throw ValidationException::withMessages(['points' => __('low_points_error')]);
            }
        } elseif ($points && (!$customer || ($points && $customer->points < $points))) {
            throw ValidationException::withMessages(['points' => __('low_points_error')]);
        }
    }

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'details'     => 'nullable',
            'expiry_date' => 'nullable|date',
            'amount'      => 'nullable|numeric',
            'points'      => 'nullable|numeric',
            'customer_id' => 'nullable|exists:customers,id',
            'number'      => 'bail|required|unique:gift_cards,number,' . $this->id,
        ];
    }
}
