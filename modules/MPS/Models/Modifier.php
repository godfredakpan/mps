<?php

namespace Modules\MPS\Models;

use Modules\MPS\Models\Traits\HasManySyncable;

class Modifier extends Base
{
    use HasManySyncable;

    public static $searchable = ['id', 'code', 'title', 'details', 'show_as', 'extra_attributes'];

    protected $casts = ['extra_attributes' => 'array'];

    protected $fillable = ['code', 'title', 'details', 'show_as', 'extra_attributes'];

    protected $with = ['options'];

    public function del()
    {
        $this->items()->detach();
        $this->options()->delete();
        return $this->delete();
    }

    public function items()
    {
        return $this->belongsToMany(Item::class);
    }

    public function options()
    {
        return $this->hasMany(ModifierOption::class);
    }

    public static function scopeSearch($query, $search)
    {
        if (!empty($search)) {
            $query->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%");
            });
        }
        return $query;
    }
}
