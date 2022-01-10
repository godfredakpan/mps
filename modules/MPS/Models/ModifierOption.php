<?php

namespace Modules\MPS\Models;

class ModifierOption extends Base
{
    protected $fillable = ['sku', 'item_name', 'item_id', 'quantity'];

    protected $with = ['item'];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function modifier()
    {
        return $this->belongsTo(Modifier::class);
    }
}
