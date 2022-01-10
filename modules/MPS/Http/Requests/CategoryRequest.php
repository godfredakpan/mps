<?php

namespace Modules\MPS\Http\Requests;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    public function authorize()
    {
        return true; // TODO: only if admin
    }

    public function rules()
    {
        return [
            'name'      => 'required',
            'photo'     => 'nullable|max:1024',
            'code'      => 'bail|required|alpha_dash|unique:categories,code,' . $this->id,
            'parent_id' => 'bail|nullable|exists:categories,id',
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
