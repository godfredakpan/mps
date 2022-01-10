<?php

namespace Modules\MPS\Http\Requests;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class ItemRequest extends FormRequest
{
    public function authorize()
    {
        return true; // TODO:
    }

    public function rules()
    {
        return [
            'code'                                => 'bail|required|alpha_dash|unique:items,code,' . $this->id,
            'type'                                => 'nullable|in:standard,service,recipe,combo,download',
            'name'                                => 'required',
            'photo'                               => 'nullable',
            'video'                               => 'nullable|url',
            'images.*'                            => 'nullable|mimes:jpg,png|max:2048',
            'details'                             => 'nullable',
            'summary'                             => 'nullable',
            'alt_name'                            => 'nullable',
            'sku'                                 => 'nullable|unique:items,sku,' . $this->id,
            'rack'                                => 'nullable',
            'min_price'                           => 'nullable',
            'max_price'                           => 'nullable',
            'symbology'                           => 'required',
            'changeable'                          => 'nullable',
            'is_service'                          => 'nullable',
            'taxes'                               => 'nullable|array',
            'tax_included'                        => 'nullable',
            'cost'                                => 'required|numeric',
            'max_discount'                        => 'nullable',
            'price'                               => 'required|numeric',
            'expiry'                              => 'nullable|boolean',
            'is_stock'                            => 'nullable|boolean',
            'has_variants'                        => 'nullable|boolean',
            'variants'                            => 'nullable|required_if:has_variants,true|array',
            'variations'                          => 'nullable|array',
            'variations.*.meta'                   => 'nullable|array',
            'modifiers'                           => 'nullable|array',
            'brand_id'                            => 'nullable',
            'unit_id'                             => 'nullable',
            'sale_unit_id'                        => 'nullable',
            'purchase_unit_id'                    => 'nullable',
            'weight'                              => 'nullable',
            'dimensions'                          => 'nullable',
            'extra_attributes'                    => 'nullable',
            'hide_in_pos'                         => 'nullable',
            'hide_in_shop'                        => 'nullable',
            'stock.*.location_id'                 => 'nullable',
            'stock.*.rack'                        => 'nullable|string',
            'stock.*.cost'                        => 'nullable|numeric',
            'stock.*.price'                       => 'nullable|numeric',
            'stock.*.quantity'                    => 'nullable|numeric',
            'category_id'                         => 'bail|required|exists:categories,id',
            'supplier_id'                         => 'bail|nullable|exists:suppliers,id',
            'supplier_item_id'                    => 'nullable|unique:items,supplier_item_id,' . $this->id,
            'has_serials'                         => 'nullable',
            'serials'                             => 'nullable|array',
            'portions'                            => 'array|min:1|required_if:type,combo|required_if:type,recipe',
            'portions.*.portion_items'            => 'array|min:1|required_if:type,recipe',
            'portions.*.portion_items.*.item_id'  => 'required_if:type,recipe',
            'portions.*.portion_items.*.quantity' => 'required_if:type,recipe',
        ];
    }

    public function validated()
    {
        $data = $this->validator->validated();
        if ($this->has('photo') && $this->photo && !Str::endsWith($this->photo, 'images/dummy.jpg')) {
            $path          = $this->photo->store('/images', 'public');
            $data['photo'] = Storage::disk('public')->url($path);
        }
        unset($data['images']);
        return $data;
    }

    public function withValidator($validator)
    {
        $validator->setImplicitAttributesFormatter(function ($attribute) {
            $attributes = explode('.', $attribute);
            if ($attributes[0] == 'portions') {
                if ($attributes[2]) {
                    $relations = explode('_', $attributes[2]);
                    Log::info('field', $relations);
                    return 'portion ' . ((int) $attributes[1] + 1) . ' ' . $relations[1] . ' ' . (isset($relations[3]) ? $relations[2] : '');
                }
                return 'portion ' . ((int) $attributes[1] + 1);
            }
            return $attributes;
        });
    }

    protected function passedValidation()
    {
        // $item = $this->route('item');
        if ($this->has_serials) {
            $serials = collect($this->serials)->count();
            $stock   = collect($this->stock)->sum('stock');
            if ($stock != $serials) {
                throw ValidationException::withMessages(['serials' => __choice('serials_and_stock_not_match', ['stock' => $stock, 'serials' => $serials])]);
            }
        }
    }
}
