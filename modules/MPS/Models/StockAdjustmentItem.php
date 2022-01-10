<?php

namespace Modules\MPS\Models;

use Modules\MPS\Models\Traits\HasTaxes;
use Modules\MPS\Models\Traits\HasManySyncable;

class StockAdjustmentItem extends Base
{
    use HasManySyncable;
    use HasTaxes;

    public $hasLocation = true;

    protected $casts = ['item_taxes' => 'object'];

    protected $fillable = [
        'name', 'code', 'quantity', 'cost', 'unit_cost', 'subtotal', 'item_taxes', 'unit_id',
        'item_id', 'tax_amount', 'total_tax_amount', 'net_cost', 'discount', 'batch_no', 'expiry_date',
        'discount_amount', 'total_discount_amount', 'balance', 'base_net_cost', 'base_unit_cost', 'expired_quantity',
    ];

    public function adjustment()
    {
        return $this->belongsTo(StockAdjustment::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function scopeWithAdjustment($query, $location)
    {
        $query->with(['item' => fn ($q) => $q->withAdjustment($location), 'variations']);
    }

    public function serials()
    {
        return $this->belongsToMany(Serial::class);
    }

    public function stock($location_id = null)
    {
        return $this->hasMany(Stock::class, 'item_id', 'item_id')->ofLocation($location_id ? $location_id : $this->location_id);
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
        return $this->belongsToMany(Variation::class)
            ->withPivot(
                'cost',
                'quantity',
                'net_cost',
                'tax_amount',
                'discount_amount',
                'total_tax_amount',
                'total_discount_amount',
                'total'
            );
    }

    protected static function boot()
    {
        parent::boot();
        static::deleted(function ($stock_adjustment) {
            $stock_adjustment->serials()->delete();
            $stock_adjustment->variations()->delete();
            $stock_adjustment->stockTrails()->delete();
        });
    }
}
