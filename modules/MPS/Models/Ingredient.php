<?php

namespace Modules\MPS\Models;

use Modules\MPS\Models\Traits\HasTaxes;

class Ingredient extends Base
{
    use HasTaxes;

    public static $searchable = ['id', 'sku', 'code', 'name', 'details', 'cost', 'price', 'quantity', 'extra_attributes'];

    protected $casts = ['extra_attributes' => 'array'];

    protected $fillable = ['sku', 'code', 'name', 'details', 'cost', 'price', 'quantity', 'extra_attributes'];

    public function items()
    {
        return $this->belongsToMany(Item::class);
    }
}
