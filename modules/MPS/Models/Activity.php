<?php

namespace Modules\MPS\Models;

use App\Activity as ActivityModel;

class Activity extends ActivityModel
{
    public static $searchable = ['created_at', 'log_name', 'description', 'subject_type'];

    public function user()
    {
        return $this->belongsTo(\Modules\MPS\Models\User::class, 'causer_id');
    }
}
