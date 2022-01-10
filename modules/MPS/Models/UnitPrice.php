<?php

namespace Modules\MPS\Models;

class UnitPrice extends Base
{
    protected $fillable = ['cost', 'price', 'unit_id', 'subject_id', 'subject_type'];

    public function subject()
    {
        return $this->morphTo();
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
