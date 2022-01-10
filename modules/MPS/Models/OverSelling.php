<?php

namespace Modules\MPS\Models;

use Ramsey\Uuid\Uuid;

class OverSelling extends Base
{
    // public $incrementing = false;

    protected $guarded = [];

    // protected $keyType = 'string';

    // protected static function boot()
    // {
    //     parent::boot();
    //     static::creating(function ($model) {
    //         $model->id = Uuid::uuid4()->toString();
    //     });
    // }

    public function item()
    {
        return $this->belongsTo(Item::class);
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
