<?php

namespace Modules\MPS\Models;

use Illuminate\Database\Eloquent\Builder;
use Modules\MPS\Models\Traits\HasAttachments;
use Modules\MPS\Models\Traits\HasManySyncable;

class Delivery extends Base
{
    use HasAttachments;
    use HasManySyncable;

    public $hasNumber = true;

    public $hasReference = true;

    public $hasUser = true;

    public static $searchable = [
        'id', 'date', 'details', 'status', 'reference', 'driver', 'delivered_at',
        'delivered_by', 'received_by', 'sale_id', 'customer_id', 'customer.name',
        'user_id', 'user.name', 'number',
    ];

    protected $casts = ['date' => 'date'];

    protected $fillable = [
        'date', 'status', 'location_id', 'sale_id', 'customer_id', 'details',
        'reference', 'driver', 'delivered_at', 'delivered_by', 'received_by',
    ];

    protected $with = ['customer', 'location', 'user'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function del()
    {
        return $this->delete();
    }

    public function deliveryMan()
    {
        return $this->belongsTo(User::class, 'delivery_id');
    }

    public function items()
    {
        return $this->hasMany(DeliveryItem::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function withAll()
    {
        return $this->loadMissing([
            'sale', 'items' => fn ($q) => $q->withAll($this->from),
        ]);
    }

    public function withItems()
    {
        return $this->loadMissing([
            'items' => fn ($q) => $q->withAll($this->from),
        ]);
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
        static::deleted(function ($delivery) {
            $delivery->items->each->delete();
        });
    }
}
