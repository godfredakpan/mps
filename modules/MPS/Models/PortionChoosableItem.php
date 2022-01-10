<?php

namespace Modules\MPS\Models;

class PortionChoosableItem extends Base
{
    public $timestamps = false;

    protected $fillable = ['item_id', 'portion_choosable_id', 'variation_id', 'quantity'];

    protected $with = ['item.variations'];

    public function choosable()
    {
        return $this->belongsTo(PortionChoosable::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function variation()
    {
        return $this->belongsTo(Variation::class);
    }
}
