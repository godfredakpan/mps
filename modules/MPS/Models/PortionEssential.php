<?php

namespace Modules\MPS\Models;

class PortionEssential extends Base
{
    public $timestamps = false;

    protected $fillable = ['item_id', 'portion_id', 'variation_id', 'quantity'];

    protected $with = ['item.variations'];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function portion()
    {
        return $this->belongsTo(Portion::class);
    }

    public function variation()
    {
        return $this->belongsTo(Variation::class);
    }
}
