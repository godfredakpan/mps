<?php

namespace Modules\MPS\Models;

use Illuminate\Support\Facades\Cache;
use Modules\MPS\Models\Traits\HasManySyncable;
use Modules\MPS\Models\Traits\HasSchemalessAttributes;

class Location extends Base
{
    use HasManySyncable;
    use HasSchemalessAttributes;

    public static $searchable = [
        'id', 'phone', 'color', 'header', 'footer', 'address', 'name', 'email', 'account.name', 'logo', 'timezone',
        'extra_attributes', 'state', 'country', 'state_name', 'country_name', 'house_no', 'street_no', 'city', 'postal_code'
    ];

    protected $casts = ['extra_attributes' => 'array'];

    protected $fillable = [
        'phone', 'color', 'header', 'footer', 'address', 'name', 'email', 'account_id', 'logo', 'timezone',
        'extra_attributes', 'state', 'country', 'state_name', 'country_name', 'house_no', 'street_no', 'city', 'postal_code'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function accounts()
    {
        return $this->belongsToMany(Account::class);
    }

    public function del()
    {
        if ($this->registerRecords()->exists()) {
            return false;
        }
        $this->registers()->delete();
        $this->accounts()->detach();
        return $this->delete();
    }

    public function registerRecords()
    {
        return $this->hasMany(RegisterRecord::class);
    }

    public function registers()
    {
        return $this->hasMany(Register::class);
    }

    public static function scopeSearch($query, $search)
    {
        if (!empty($search)) {
            $query->where(function ($query) use ($search) {
                $query->where('id', 'LIKE', "%$search%")
                    ->orWhere('name', 'LIKE', "%$search%");
            });
        }
        return $query;
    }

    public function stock()
    {
        return $this->hasMany(Stock::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::created(function ($location) {
            Cache::flush();
        });
        static::updated(function ($location) {
            Cache::flush();
        });
        static::deleted(function ($location) {
            Cache::flush();
        });
    }
}
