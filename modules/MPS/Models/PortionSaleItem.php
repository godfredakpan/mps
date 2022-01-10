<?php

namespace Modules\MPS\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PortionSaleItem extends Pivot
{
    protected $casts = ['choosables' => 'object', 'essentials' => 'object', 'portion_items' => 'object'];
}
