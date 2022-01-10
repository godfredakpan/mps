<?php

namespace Modules\MPS\Models\Traits;

use Modules\MPS\Models\Stock;

trait HasStock
{
    public function stock()
    {
        return $this->hasMany(Stock::class, 'item_id', 'item_id')->where('location_id', session('location_id'));
    }
}
