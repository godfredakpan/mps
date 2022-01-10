<?php

namespace Modules\MPS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class UserMeta extends Model
{
    public $incrementing = false;

    public $timestamps = false;

    protected $casts = ['collapsed' => 'boolean'];

    protected $guarded = [];

    protected $keyType = 'string';

    protected $primaryKey = 'meta_key';

    protected $table = 'usermeta';

    public function scopeMps($query, $value = 1)
    {
        return $query->where('module', 'mps');
    }

    public function scopeOfUser($query, $user_id)
    {
        return $query->where('user_id', $user_id);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function boot()
    {
        parent::boot();
        if (auth()->user() && !auth()->user()->hasRole('super')) {
            static::addGlobalScope('user', function (Builder $builder) {
                $builder->where('user_id', auth()->id());
            });
        }
        static::creating(function ($model) {
            if (!$model->module) {
                $model->module = 'mps';
            }
        });
    }
}
