<?php

namespace Modules\MPS\Models;

class Variation extends Base
{
    public $timestamps = false;

    protected $casts = ['meta' => 'array'];

    protected $fillable = ['sku', 'code', 'item_id', 'rack', 'cost', 'price', 'quantity', 'dimensions', 'weight', 'meta'];

    protected $with = ['locationStock'];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function locationStock($location = null)
    {
        return $this->stock()->ofLocation($location);
    }

    public function stock()
    {
        return $this->hasMany(VariationStock::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::deleting(function ($variation) {
            $variation->stock()->delete();
        });
    }
}
