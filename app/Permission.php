<?php

namespace App;

use Ramsey\Uuid\Uuid;

class Permission extends \Spatie\Permission\Models\Permission
{
    public $guarded = [];

    public $incrementing = false;

    protected $hidden = ['pivot'];

    protected $keyType = 'string';

    protected $primaryKey = 'id';

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = Uuid::uuid4()->toString();
            if (!$model->guard_name) {
                $model->guard_name = 'web';
            }
        });
    }
}
