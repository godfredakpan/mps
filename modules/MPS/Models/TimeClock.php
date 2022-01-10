<?php

namespace Modules\MPS\Models;

use Illuminate\Database\Eloquent\Builder;

class TimeClock extends Base
{
    public $hasLocation = true;

    public static $searchable = ['id', 'in_time', 'out_time', 'rate', 'details', 'location_id', 'user_id'];

    protected $dates = ['in_time', 'out_time'];

    protected $fillable = ['in_time', 'out_time', 'rate', 'details', 'location_id', 'user_id'];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public static function scopeCurrent($query)
    {
        return $query->whereNull('out_time');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('mine', function (Builder $builder) {
            $user = auth()->user();
            if ($user && !$user->hasRole('super') && !$user->view_all) {
                return $builder->where('user_id', $user->id);
            }
        });
        // static::addGlobalScope('of_location', function (Builder $builder) {
        //     if ($location_id = session('location_id')) {
        //         return $builder->where('location_id', $location_id);
        //     }
        // });
    }
}
