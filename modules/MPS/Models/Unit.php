<?php

namespace Modules\MPS\Models;

class Unit extends Base
{
    public static $searchable = ['id', 'code', 'name', 'base_id', 'operator', 'operation_value'];

    protected $fillable = ['code', 'name', 'base_id', 'operator', 'operation_value'];

    public function baseUnit()
    {
        return $this->belongsTo(Unit::class, 'base_id');
    }

    public function del()
    {
        if ($this->subunits()->exists() || $this->items()->exists()) {
            return false;
        }
        return $this->delete();
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public static function scopeBase($query)
    {
        return $query->whereNULL('base_id');
    }

    public function subunits()
    {
        return $this->hasMany(Unit::class, 'base_id');
    }

    protected static function boot()
    {
        parent::boot();
        static::deleting(function ($unit) {
            $unit->subunits()->delete();
        });
    }
}
