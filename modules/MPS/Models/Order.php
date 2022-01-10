<?php

namespace Modules\MPS\Models;

use Illuminate\Database\Eloquent\Builder;
use Modules\MPS\Models\Traits\HasSchemalessAttributes;

class Order extends Base
{
    use HasSchemalessAttributes;

    public $hasLocation = true;

    public $hasUser = true;

    public static $searchable = [
        'id', 'oId', 'date', 'reference', 'user_id', 'details', 'customer_id', 'grand_total', 'items',
        'taxes', 'discount', 'discount_amount', 'total_items', 'total_quantity', 'extra_attributes',
    ];

    protected $casts = ['date' => 'date', 'items' => 'array', 'taxes' => 'array', 'extra_attributes' => 'array'];

    protected $fillable = [
        'oId', 'date', 'reference', 'user_id', 'details', 'customer_id', 'grand_total', 'items', 'taxes',
        'created_by', 'discount', 'discount_amount', 'total_items', 'total_quantity', 'extra_attributes',
    ];

    protected static $logAttributes = [
        'oId', 'date', 'reference', 'user_id', 'details', 'customer_id', 'grand_total',
        'taxes', 'discount', 'discount_amount', 'total_items', 'total_quantity', 'extra_attributes',
    ];

    protected static $logFillable = false;

    protected $with = ['customer', 'user:id,name,username', 'location'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    // protected static $recordEvents = ['created', 'deleted'];

    public static function scopeMine($query)
    {
        return $query->where('user_id', auth()->id());
    }

    public function user()
    {
        return $this->belongsTo(\Modules\MPS\Models\User::class);
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
        static::addGlobalScope('of_location', function (Builder $builder) {
            if ($location_id = session('location_id')) {
                return $builder->where('location_id', $location_id);
            }
        });
    }
}
