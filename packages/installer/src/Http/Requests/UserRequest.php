<?php

namespace Tecdiary\Installer\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function messages()
    {
        return [
            'name.regex'     => 'Only alpha numeric, space, dash, underscore and dot are allowed.',
            'username.regex' => 'Only alpha numeric, dash, underscore and dot are allowed.',
        ];
    }

    public function rules()
    {
        return [
            'name' => [
                'required', 'string', 'max:50', 'regex:/^[\w\-_.\&\s]*$/',
            ],
            'username' => [
                'required', 'max:25', 'unique:users', 'regex:/^[\w\-_.]*$/',
            ],
            'email'    => 'bail|required|string|email|max:90|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ];
    }
}
