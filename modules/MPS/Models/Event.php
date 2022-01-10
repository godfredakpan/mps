<?php

namespace Modules\MPS\Models;

class Event extends Base
{
    public $hasUser = true;

    public static $searchable = ['id', 'code', 'name', 'details', 'order', 'slug'];

    protected $fillable = ['title', 'details', 'start_date', 'end_date', 'start_time', 'end_time', 'color', 'user_id'];

    protected $hidden = ['created_at', 'updated_at'];

    public function del()
    {
        return $this->delete();
    }

    public static function scopeSearch($query, $search)
    {
        if (!empty($search)) {
            $query->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%");
                $query->orWhere('details', 'like', "%{$search}%");
            });
        }
        return $query;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
