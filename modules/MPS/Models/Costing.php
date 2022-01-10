<?php

namespace Modules\MPS\Models;

class Costing extends Base
{
    protected $guarded = [];

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

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function saleItem()
    {
        return $this->belongsTo(SaleItem::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function variation()
    {
        return $this->belongsTo(Variation::class);
    }
}
