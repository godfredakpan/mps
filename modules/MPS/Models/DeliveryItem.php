<?php

namespace Modules\MPS\Models;

use Modules\MPS\Models\Traits\HasManySyncable;

class DeliveryItem extends Base
{
    use HasManySyncable;

    protected $fillable = ['code', 'name', 'delivery_id', 'item_id', 'unit_id', 'quantity', 'batch_no', 'expiry_date'];

    public function delivery()
    {
        return $this->belongsTo(Delivery::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function modifierOptions()
    {
        return $this->belongsToMany(ModifierOption::class)->withPivot('quantity');
    }

    public function portions()
    {
        return $this->belongsToMany(Portion::class)
            ->using(DeliveryItemPortion::class)
            ->withPivot('quantity', 'choosables');
    }

    public function scopeWithAll($query)
    {
        $query->with([
            'item' => fn ($q) => $q->withDelivery(),
            'variations', 'portions', 'unit', 'modifierOptions', 'serials',
        ]);
    }

    public function serials()
    {
        return $this->belongsToMany(Serial::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function variations()
    {
        return $this->belongsToMany(Variation::class)->withPivot('quantity');
    }

    public function withAll()
    {
        $this->with(['item', 'variations', 'portions', 'unit', 'modifierOptions', 'serials']);
    }

    protected static function boot()
    {
        parent::boot();
        static::deleted(function ($delivery) {
            $delivery->serials()->delete();
            $delivery->portions()->delete();
            $delivery->variations()->delete();
            $delivery->modifierOptions()->delete();
        });
    }
}
