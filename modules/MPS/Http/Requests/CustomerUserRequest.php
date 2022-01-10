<?php

namespace Modules\MPS\Http\Requests;

use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Http\FormRequest;

class CustomerUserRequest extends FormRequest
{
    public function authorize()
    {
        return true; // TODO: only if admin
    }

    public function rules()
    {
        return [
            'active'      => 'nullable',
            'name'        => 'required|string|max:255',
            'customer_id' => 'required|exists:customers,id',
            'password'    => $this->id ? 'nullable' : 'required|string|min:6|confirmed',
            'phone'       => 'required_without:email|nullable|unique:users,phone,' . $this->id,
            'username'    => 'bail|required|max:30|regex:/^[\w\-\_\.\@]*$/|unique:users,username,' . $this->id,
            'email'       => 'required_without:phone|nullable|email|string|max:255|unique:users,email,' . $this->id,
        ];
    }

    public function validated()
    {
        $data = $this->validator->validated();

        $data['employee'] = 0;
        if ($data['password'] ?? null) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        // $data['email_verified_at'] = now();
        return $data;
    }
}
