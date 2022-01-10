<?php

namespace Modules\MPS\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class StockTrail extends Base
{
    public static $searchable = [
        'id', 'created_at', 'item_id', 'unit_id', 'location.name', 'quantity', 'type', 'memo',
        'portion.name', 'variation.code',
    ];

    protected $fillable = [
        'item_id', 'location_id', 'quantity', 'type', 'memo', 'portion_id', 'variation_id', 'modifier_id', 'unit_id',
    ];

    protected $with = ['item:id,name', 'location:id,name', 'unit:id,name', 'variation:id,code'];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function portion()
    {
        return $this->belongsTo(Portion::class);
    }

    public function referencesObject($object)
    {
        $this->subject_id   = $object->id;
        $this->subject_type = get_class($object);
        $this->save();
        return $this;
    }

    public function scopeBefore($query, Carbon $date)
    {
        return $query->where('created_at', '<=', $date);
    }

    public function scopeOfLocation($query, $location = null)
    {
        return $query->where('location_id', $location ?: session('location_id'));
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function subject()
    {
        return $this->morphTo();
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function variation()
    {
        return $this->belongsTo(Variation::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('of_location', function (Builder $builder) {
            if ($location_id = session('location_id')) {
                return $builder->where('location_id', $location_id);
            }
        });
    }
}
