<?php

namespace Modules\MPS\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class DeliveryItemPortion extends Pivot
{
    protected $casts = ['choosables' => 'object'];
}
