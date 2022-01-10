<?php

namespace Modules\MPS\Models;

class Serial extends Base
{
    public $timestamps = false;

    protected $fillable = ['number', 'sold', 'item_id', 'sale_id', 'sale_item_id', 'purchase_id', 'purchase_item_id'];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function purchaseItem()
    {
        return $this->belongsTo(PurchaseItem::class);
    }

    public function scopeAvailable($query)
    {
        return $query->whereNull('sold')->orWhere('sold', 0);
    }

    public function scopeInitial($query)
    {
        return $query->whereNULL('purchase_id');
    }

    public function scopeSold($query)
    {
        return $query->where('sold', 1);
    }
}
