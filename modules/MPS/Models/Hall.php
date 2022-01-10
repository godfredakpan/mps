<?php

namespace Modules\MPS\Models;

class Hall extends Base
{
    public $hasLocation = true;

    public static $searchable = ['id', 'code', 'title', 'details', 'location_id', 'extra_attributes'];

    protected $casts = ['extra_attributes' => 'array'];

    protected $fillable = ['code', 'title', 'details', 'location_id', 'extra_attributes'];

    public function del()
    {
        if ($this->tables()->exists()) {
            return false;
        }

        return $this->delete();
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public static function scopeSearch($query, $search)
    {
        if (!empty($search)) {
            $query->where(function ($query) use ($search) {
                $query->where('id', 'like', "%{$search}%");
                $query->orWhere('title', 'like', "%{$search}%");
            });
        }
        return $query;
    }

    public function tables()
    {
        return $this->hasMany(HallTable::class)->orderBy('code');
    }
}
