<?php

namespace Modules\MPS\Models;

use Illuminate\Support\Facades\Cache;

class Field extends Base
{
    public static $searchable = ['id', 'name', 'slug', 'type', 'order', 'required', 'options', 'description', 'entities'];

    protected $casts = ['entities' => 'array'];

    protected $fillable = ['name', 'slug', 'type', 'order', 'required', 'options', 'description', 'entities'];

    public function del()
    {
        return $this->delete();
    }

    public function fieldable()
    {
        return $this->morphTo();
    }

    protected static function boot()
    {
        parent::boot();
        static::created(function ($field) {
            Cache::flush();
        });
        static::updated(function ($field) {
            Cache::flush();
        });
        static::deleted(function ($field) {
            Cache::flush();
        });
    }
}
