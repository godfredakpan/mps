<?php

namespace Modules\MPS\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PortionReturnOrderItem extends Pivot
{
    protected $casts = ['choosables' => 'object', 'essentials' => 'object', 'portion_items' => 'object'];
}
