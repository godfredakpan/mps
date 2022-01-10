<?php

namespace Modules\MPS\Http\Requests;

use App\User;
use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    public function authorize()
    {
        return true; // TODO: only if admin
    }

    public function rules()
    {
        return [
            'active'           => 'nullable',
            'view_all'         => 'nullable',
            'edit_all'         => 'nullable',
            'bulk_actions'     => 'nullable',
            'employee'         => 'nullable',
            'can_impersonate'  => 'nullable',
            'roles'            => 'required_without:customer_id|array',
            'locations'        => 'nullable|array',
            'address'          => 'nullable|string',
            'name'             => 'required|string|max:255',
            'vendor_id'        => 'nullable|exists:vendors,id',
            'customer_id'      => 'nullable|exists:customers,id',
            'location_id'      => 'nullable|exists:locations,id',
            'password'         => $this->id ? 'nullable' : 'required|string|min:6|confirmed',
            'phone'            => 'required_without:email|nullable|unique:users,phone,' . $this->id,
            'username'         => 'bail|required|max:30|regex:/^[\w\-\_\.]*$/|unique:users,username,' . $this->id,
            'email'            => 'required_without:phone|nullable|email|string|max:255|unique:users,email,' . $this->id,
            'extra_attributes' => 'nullable',

            'files.*.name' => 'nullable',
            'files.*.file' => 'nullable',
            'settings'     => 'nullable|array',
        ];
    }

    public function validated()
    {
        $data = $this->validator->validated();

        $data['settings']['mps']    = 1;
        $data['settings']['number'] = $data['settings']['number'] ?? 'E' . (User::count() + 1);
        return $data;
    }
}
