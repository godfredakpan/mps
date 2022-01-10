<?php

namespace Modules\MPS\Models\Traits;

use Modules\MPS\Models\Promotion;

trait HasPromotions
{
    public function promotions()
    {
        return $this->belongsToMany(Promotion::class);
    }

    public function validPromotions()
    {
        return $this->promotions()->valid();
    }
}
