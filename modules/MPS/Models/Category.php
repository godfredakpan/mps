<?php

namespace Modules\MPS\Models;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Modules\MPS\Models\Traits\CategoryTrait;
use Modules\MPS\Models\Traits\HasPromotions;
use Modules\MPS\Models\Traits\HasSchemalessAttributes;

class Category extends Base
{
    use CategoryTrait;
    use HasPromotions;
    use HasSchemalessAttributes;
    use HasSlug;

    public static $searchable = ['id', 'code', 'name'];

    protected $casts = ['extra_attributes' => 'array'];

    protected $fillable = ['code', 'name', 'slug', 'parent_id', 'order', 'photo'];

    protected $hidden = ['created_at', 'updated_at', 'pivot'];

    public function expenses()
    {
        return $this->morphedByMany(Expense::class, 'categorizable');
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
        ->generateSlugsFrom('name')
        ->saveSlugsTo('slug')
        ->doNotGenerateSlugsOnUpdate();
    }

    public function incomes()
    {
        return $this->morphedByMany(Income::class, 'categorizable');
    }

    public function items()
    {
        return $this->morphedByMany(Item::class, 'categorizable');
    }

    protected static function boot()
    {
        parent::boot();
        static::deleting(function ($category) {
            $category->children()->delete();
        });
    }
}
