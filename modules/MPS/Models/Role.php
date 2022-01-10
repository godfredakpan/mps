<?php

namespace Modules\MPS\Models;

use App\Role as BaseRole;
use Modules\MPS\Models\Traits\TableTrait;
use Modules\MPS\Models\Traits\ActivityTrait;

class Role extends BaseRole
{
    use ActivityTrait;
    use TableTrait;

    public static $searchable = ['id', 'name', 'created_at'];

    protected $fillable = ['name'];

    public function del()
    {
        if ($this->users()->exists() || $this->permissions()->exists()) {
            return false;
        }

        return $this->delete();
    }

    public function scopeNotSuper($query)
    {
        return $query->where('name', '!=', 'super');
    }
}
