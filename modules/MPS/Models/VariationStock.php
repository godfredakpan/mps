<?php

namespace Modules\MPS\Models;

class VariationStock extends Base
{
    public $timestamps = false;

    protected $fillable = ['sku', 'location_id', 'variation_id', 'rack', 'cost', 'price', 'quantity', 'avg_cost'];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function scopeOfLocation($query, $location = null)
    {
        return $query->where('location_id', $location ?: session('location_id'));
    }

    public function variation()
    {
        return $this->belongsTo(Variation::class);
    }
}
