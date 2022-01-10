<?php

namespace Modules\MPS\Models;

class Portion extends Base
{
    public static $searchable = ['id', 'sku', 'name', 'cost', 'price', 'item_id'];

    protected $fillable = ['sku', 'name', 'cost', 'price', 'item_id'];

    protected $with = ['choosables.items', 'essentials', 'portionItems'];

    public function choosables()
    {
        return $this->hasMany(PortionChoosable::class);
    }

    public function essentials()
    {
        return $this->hasMany(PortionEssential::class);
    }

    public function items()
    {
        return $this->belongsToMany(Item::class);
    }

    public function portionItems()
    {
        return $this->hasMany(PortionItem::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::deleting(function ($portion) {
            $portion->choosables->each(function ($choosable) {
                $choosable->items()->delete();
            });
            $portion->essentials()->delete();
            $portion->choosables()->delete();
            $portion->portionItems()->delete();
        });
    }
}
