<?php

namespace Modules\MPS\Models;

use App\Permission as BasePermission;

class Permission extends BasePermission
{
    public static $searchable = ['id', 'name', 'created_at'];

    protected $fillable = ['name'];

    public function del()
    {
        if ($this->users()->exists()) {
            return false;
        }

        return $this->delete();
    }
}
