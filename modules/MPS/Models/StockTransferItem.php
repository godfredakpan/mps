<?php

namespace Modules\MPS\Models;

use Modules\MPS\Models\Traits\HasManySyncable;

class StockTransferItem extends Base
{
    use HasManySyncable;

    protected $fillable = ['code', 'name', 'stock_transfer_id', 'item_id', 'unit_id', 'quantity', 'batch_no', 'expiry_date'];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function scopeWithTransfer($query, $location)
    {
        $query->with(['item' => fn ($q) => $q->withTransfer($location), 'variations']);
    }

    public function serials()
    {
        return $this->belongsToMany(Serial::class);
    }

    public function stock()
    {
        return $this->hasMany(Stock::class, 'item_id', 'item_id');
    }

    public function stockTrails()
    {
        return $this->morphMany(StockTrail::class, 'subject');
    }

    public function transfer()
    {
        return $this->belongsTo(StockTransfer::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function variations()
    {
        return $this->belongsToMany(Variation::class)->withPivot('quantity');
    }

    protected static function boot()
    {
        parent::boot();
        static::deleted(function ($stock_transfer) {
            // $stock_transfer->stock()->delete();
            $stock_transfer->serials()->delete();
            $stock_transfer->variations()->delete();
            $stock_transfer->stockTrails()->delete();
        });
    }
}
