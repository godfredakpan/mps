<?php

namespace Modules\MPS\Models;

class Register extends Base
{
    protected $fillable = ['code', 'name', 'location_id', 'device_id', 'terminal_id'];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function registerRecords()
    {
        return $this->hasMany(RegisterRecord::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function users()
    {
        return $this->belongsToMany(\Modules\MPS\Models\User::class)->withTimestamps();
    }
}
