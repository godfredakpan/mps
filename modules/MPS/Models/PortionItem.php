<?php

namespace Modules\MPS\Models;

class PortionItem extends Base
{
    public $timestamps = false;

    protected $fillable = ['quantity', 'item_id', 'portion_id', 'variation_id'];

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
