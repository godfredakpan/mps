<?php

namespace App;

use Ramsey\Uuid\Uuid;
use Modules\MPS\Models\Traits\TableTrait;
use Spatie\Activitylog\Models\Activity as ActivityModel;

class Activity extends ActivityModel
{
    use TableTrait;

    public static $columns = ['id', 'created_at', 'description', 'subject_id', 'subject_type', 'causer_id', 'causer_type'];

    public $incrementing = false;

    protected $keyType = 'string';

    protected $with = ['user:id,name,username'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = Uuid::uuid4()->toString();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'causer_id');
    }
}
