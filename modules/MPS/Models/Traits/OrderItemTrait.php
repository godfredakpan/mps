<?php

namespace Modules\MPS\Models\Traits;

use Modules\MPS\Models\Unit;
use Modules\MPS\Models\Serial;
use Modules\MPS\Models\Portion;
use Modules\MPS\Models\Variation;
use Modules\MPS\Models\StockTrail;
use Modules\MPS\Models\ModifierOption;
use Modules\MPS\Models\PortionSaleItem;

trait OrderItemTrait
{
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
            ->using(PortionSaleItem::class)
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
                'choosables',
                'essentials',
                'portionItems',
                'portion_items'
            );
    }

    public function scopeWithAll($query)
    {
        $query->with([
            'item' => fn ($q) => $q->select(['id', 'code', 'name', 'alt_name', 'tax_included'])->withAll(),
            'promotions', 'variations', 'portions', 'unit.subunits', 'modifierOptions.modifier', 'serials',
        ]);
    }

    public function scopeWithRecurring($query)
    {
        $query->with([
            'item' => fn ($q) => $q->select(['id', 'code', 'name', 'alt_name', 'tax_included'])->withAll(),
            'promotions', 'variations', 'portions', 'unit.subunits', 'modifierOptions',
        ]);
    }

    public function scopeWithSelected($query)
    {
        $query->with(['item', 'promotions', 'variations', 'portions', 'unit.subunits', 'modifierOptions']);
    }

    public function serials()
    {
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
