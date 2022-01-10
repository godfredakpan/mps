<?php

namespace Modules\MPS\Models;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Brand extends Base
{
    use HasSlug;

    public static $searchable = ['id', 'code', 'name', 'details', 'order', 'slug'];

    protected $fillable = ['code', 'name', 'details', 'order', 'slug', 'photo'];

    public function del()
    {
        if ($this->items()->exists()) {
            return false;
        }
        return $this->delete();
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public static function scopeSearch($query, $search)
    {
        if (!empty($search)) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
                $query->orWhere('code', 'like', "%{$search}%");
            });
        }
        return $query;
    }
}
