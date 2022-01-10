<?php

namespace Modules\MPS\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'first_name'  => 'required|max:30',
            'last_name'   => 'required|max:30',
            'email'       => 'nullable|email',
            'phone'       => 'required|max:30',
            'country'     => 'required',
            'state'       => 'required',
            'house_no'    => 'required|max:190',
            'street_no'   => 'required|max:190',
            'company'     => 'nullable|max:190',
            'address'     => 'required|max:190',
            'city'        => 'required|max:30',
            'postal_code' => 'required|max:30',
            'default'     => 'nullable|boolean',
        ];
    }

    public function validated()
    {
        $data = $this->validator->validated();
        if (!empty($data['country']) && !empty($data['state'])) {
            $cs                   = getCS($data['country'], $data['state']);
            $data['state_name']   = $cs['state']->name;
            $data['country_name'] = $cs['country']->name;
        }
        if (!$this->id) {
            $data['user_id'] = auth()->id();
        }

        return $data;
    }
}
