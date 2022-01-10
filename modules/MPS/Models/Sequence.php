<?php

namespace Modules\MPS\Models;

use Illuminate\Database\Eloquent\Model;

class Sequence extends Model
{
    public $timestamps = false;

    protected $dates = ['last_reset_date'];

    protected $guarded = [];
}
