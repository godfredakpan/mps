<?php

namespace Modules\MPS\Models;

class CustomerGroup extends Base
{
    public static $searchable = ['id', 'name', 'code', 'discount'];

    protected $fillable = ['name', 'code', 'discount'];

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function del()
    {
        // TODO
        // if ($this->customers()->exists()) {
        //     return false;
        // }

        return $this->delete();
    }

    public static function scopeSearch($query, $search)
    {
        if (!empty($search)) {
            $query->where(function ($query) use ($search) {
                $query->where('id', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }
        return $query;
    }
}
