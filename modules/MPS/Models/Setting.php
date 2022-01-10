<?php

namespace Modules\MPS\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    protected $keyType = 'string';

    protected $primaryKey = 'mps_key';

    protected $table = 'mps_settings';
}
