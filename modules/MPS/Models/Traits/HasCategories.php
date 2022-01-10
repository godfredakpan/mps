<?php

namespace Modules\MPS\Models\Traits;

use Modules\MPS\Models\Category;

trait HasCategories
{
    public function categories()
    {
        return $this->morphToMany(Category::class, 'categorizable');
    }
}
