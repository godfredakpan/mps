<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $guarded = [];

    protected $keyType = 'string';

    protected $primaryKey = 'tec_key';
}
