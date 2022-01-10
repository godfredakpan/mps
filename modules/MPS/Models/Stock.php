<?php

namespace Modules\MPS\Models;

class Stock extends Base
{
    public $hasLocation = true;

    public $timestamps = false;

    protected $fillable = ['location_id', 'item_id', 'cost', 'price', 'quantity', 'rack', 'alert_quantity', 'avg_cost'];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function scopeOfItem($query, $item_id)
    {
        return $query->where('item_id', $item_id);
    }

    public function scopeOfLocation($query, $location = null)
    {
        return $query->where('location_id', $location ?: session('location_id'));
    }
}
