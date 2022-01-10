<?php

namespace Modules\MPS\Models;

use Modules\MPS\Models\Traits\HasTaxes;
use Illuminate\Database\Eloquent\Builder;
use Modules\MPS\Models\Traits\HasAttachments;
use Modules\MPS\Models\Traits\HasManySyncable;
use Modules\MPS\Models\Traits\HasSchemalessAttributes;

class RecurringSale extends Base
{
    use HasAttachments;
    use HasManySyncable;
    use HasSchemalessAttributes;
    use HasTaxes;

    public $hasHash = true;

    public $hasLocation = true;

    public $hasNumber = true;

    public $hasReference = true;

    public $hasUser = true;

    public static $searchable = [
        'id', 'created_at', 'updated_at',
        'start_date', 'repeat', 'reference', 'discount', 'discount_amount', 'total_tax_amount', 'order_tax_amount',
        'item_tax_amount', 'recoverable_tax_amount', 'recoverable_tax_calculated_on', 'shipping', 'total', 'grand_total',
        'details', 'hash', 'create_before', 'last_created_at', 'draft', 'payment', 'customer_id', 'location_id', 'user_id', 'extra_attributes', 'number',
    ];

    protected $casts = ['extra_attributes' => 'array'];

    protected $dates = ['last_created_at', 'start_date'];

    protected $fillable = [
        'start_date', 'repeat', 'reference', 'discount', 'discount_amount', 'total_tax_amount', 'order_tax_amount',
        'item_tax_amount', 'recoverable_tax_amount', 'recoverable_tax_calculated_on', 'shipping', 'total', 'grand_total',
        'details', 'hash', 'create_before', 'last_created_at', 'draft', 'payment', 'customer_id', 'location_id', 'user_id', 'extra_attributes',
    ];

    protected $with = ['attachments', 'customer', 'user:id,name,username'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function del()
    {
        if ($this->sales()->exists()) {
            return false;
        }

        return $this->delete();
    }

    public function items()
    {
        return $this->hasMany(RecurringSaleItem::class)->orderBy('order');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function payments()
    {
        return $this->belongsToMany(Payment::class);
    }

    public function routeNotificationForMail($notification)
    {
        return $this->customer->email;
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public static function scopeActive($query)
    {
        return $query->where('draft', '!=', 1);
    }

    public static function scopeDraft($query)
    {
        return $query->whereNull('draft')->orWhereDraft(0);
    }

    public static function scopeDueDays($query, $days = 7)
    {
        return $query->where('last_created_at', '>=', now()->subDays((int) $days)->format('Y-m-d'));
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
        static::deleting(function ($recurring_sale) {
            $recurring_sale->taxes()->detach();
            $recurring_sale->items()->delete();
        });
    }
}
