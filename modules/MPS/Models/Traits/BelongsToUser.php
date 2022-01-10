<?php

namespace Modules\MPS\Models\Traits;

use Modules\MPS\Models\User;

trait BelongsToUser
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
