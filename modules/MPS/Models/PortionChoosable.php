<?php

namespace Modules\MPS\Models;

class PortionChoosable extends Base
{
    public $timestamps = false;

    protected $fillable = ['name', 'portion_id'];

    protected $with = ['items'];

    public function items()
    {
        return $this->hasMany(PortionChoosableItem::class);
    }

    public function portion()
    {
        return $this->belongsTo(Portion::class);
    }
}
