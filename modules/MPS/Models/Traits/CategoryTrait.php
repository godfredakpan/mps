<?php

namespace Modules\MPS\Models\Traits;

use Modules\MPS\Models\Category;

trait CategoryTrait
{
    public function child()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->child()->with('children');
    }

    public function del()
    {
        // TODO
        // if ($this->products()->exists() || $this->invoices()->exists() || $this->purchases()->exists()) {
        //     return false;
        // }

        return $this->delete();
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public static function scopeParents($query)
    {
        return $query->whereNULL('parent_id');
    }

    public static function scopeSearch($query, $search)
    {
        if (!empty($search)) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            });
        }
        return $query;
    }
}
