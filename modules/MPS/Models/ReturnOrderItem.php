<?php

namespace Modules\MPS\Models;

use Modules\MPS\Models\Traits\HasStock;
use Modules\MPS\Models\Traits\HasTaxes;
use Modules\MPS\Models\Traits\BelongsToItem;
use Modules\MPS\Models\Traits\HasPromotions;
use Modules\MPS\Models\Traits\HasManySyncable;

class ReturnOrderItem extends Base
{
    use BelongsToItem;
    use HasManySyncable;
    use HasPromotions;
    use HasStock;
    use HasTaxes;

    protected $casts = ['item_taxes' => 'object'];

    protected $fillable = [
        'name', 'code', 'quantity', 'price', 'unit_price', 'subtotal', 'item_taxes',
        'item_id', 'tax_amount', 'total_tax_amount', 'net_price', 'discount', 'order',
        'discount_amount', 'total_discount_amount', 'unit_id', 'batch_no', 'expiry_date',
    ];

    protected $with = ['stock', 'taxes', 'promotions'];

    public function modifierOptions()
    {
        // return $this->hasMany(ModifierOptions::class);
        return $this->belongsToMany(ModifierOption::class)
            ->withPivot(
                'cost',
                'price',
                'quantity',
                'net_cost',
                'net_price',
                'tax_amount',
                'discount_amount',
                'total_tax_amount',
                'total_discount_amount',
                'total'
            );
    }

    public function portions()
    {
        // return $this->hasMany(Portion::class);
        return $this->belongsToMany(Portion::class)
            ->using(PortionReturnOrderItem::class)
            ->withPivot(
                'cost',
                'price',
                'quantity',
                'net_cost',
                'net_price',
                'tax_amount',
                'discount_amount',
                'total_tax_amount',
                'total_discount_amount',
                'total',
                'choosables'
            );
    }

    public function returnOrder()
    {
        return $this->belongsTo(ReturnOrder::class);
    }

    public function scopeWithAll($query)
    {
        $query->with(['item' => function ($q) {
            $q->withAll();
        }, 'promotions', 'variations', 'portions', 'modifierOptions']);
    }

    public function scopeWithSelected($query)
    {
        $query->with(['item', 'promotions', 'variations', 'portions', 'modifierOptions']);
    }

    public function serials()
    {
        // return $this->hasMany(Serial::class)->orderBy('number');
        return $this->belongsToMany(Serial::class);
    }

    public function stockTrails()
    {
        return $this->morphMany(StockTrail::class, 'subject');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function variations()
    {
        // return $this->hasMany(Variation::class);
        return $this->belongsToMany(Variation::class)
            ->withPivot(
                'cost',
                'price',
                'quantity',
                'net_cost',
                'net_price',
                'tax_amount',
                'discount_amount',
                'total_tax_amount',
                'total_discount_amount',
                'total'
            );
    }
}
