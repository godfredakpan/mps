<?php

namespace Modules\MPS\Http\Requests;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Http\FormRequest;

class BrandRequest extends FormRequest
{
    public function authorize()
    {
        return true; // TODO: only if admin
    }

    public function rules()
    {
        return [
            'details' => 'nullable',
            'order'   => 'nullable|numeric',
            'photo'   => 'nullable|max:1024',
            'name'    => 'bail|required|unique:units,name,' . $this->id,
            'code'    => 'bail|nullable|alpha_num|max:20|unique:units,code,' . $this->id,
        ];
    }

    public function validated()
    {
        $data = $this->validator->validated();
        if ($this->has('photo') && $this->photo && !Str::endsWith($this->photo, 'images/dummy.jpg')) {
            $path          = $this->photo->store('/images', 'public');
            $data['photo'] = Storage::disk('public')->url($path);
        }
        return $data;
    }
}
