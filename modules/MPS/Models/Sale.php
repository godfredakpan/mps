<?php

namespace Modules\MPS\Models;

use Modules\Shop\Models\Address;
use Modules\MPS\Events\SaleEvent;
use Illuminate\Support\Facades\URL;
use Modules\MPS\Models\Traits\HasTaxes;
use Illuminate\Database\Eloquent\Builder;
use Modules\MPS\Models\Traits\OfLocation;
use Modules\MPS\Models\Traits\OrderTrait;
use Modules\MPS\Models\Traits\BelongsToUser;
use Modules\MPS\Models\Traits\DirectPayments;
use Modules\MPS\Models\Traits\HasAttachments;
use Modules\MPS\Models\Traits\HasManySyncable;
use Modules\MPS\Models\Traits\BelongsToCustomer;
use Modules\MPS\Models\Traits\BelongsToLocation;
use Modules\MPS\Models\Traits\BelongsToPayments;
use Modules\MPS\Models\Traits\HasSchemalessAttributes;

class Sale extends Base
{
    use BelongsToCustomer;
    use BelongsToLocation;
    use BelongsToPayments;
    use BelongsToUser;
    use DirectPayments;
    use HasAttachments;
    use HasManySyncable;
    use HasSchemalessAttributes;
    use HasTaxes;
    use OfLocation;
    use OrderTrait;

    public $hasHash = true;

    public $hasLocation = true;

    public $hasNumber = true;

    public $hasReference = true;

    public $hasUser = true;

    public static $searchable = [
        'id', 'date', 'reference', 'customer.name', 'customer.company', 'user.name', 'orderId',
        'type', 'discount', 'total', 'grand_total', 'details', 'transaction_id', 'extra_attributes', 'number',
        'address_id', 'shipping_method_id', 'payment_method', 'coupon_id',
    ];

    protected $casts = ['date' => 'date', 'extra_attributes' => 'array'];

    protected $fillable = [
        'pos', 'shop', 'register_id', 'register_record_id', 'recurring_sale_id', 'orderId',
        'date', 'type', 'reference', 'discount', 'discount_amount', 'total_tax_amount', 'order_tax_amount',
        'item_tax_amount', 'recoverable_tax_amount', 'recoverable_tax_calculated_on', 'shipping', 'total', 'grand_total',
        'details', 'hash', 'paid', 'draft', 'customer_id', 'location_id', 'user_id', 'transaction_id', 'extra_attributes',
        'address_id', 'shipping_method_id', 'payment_method', 'coupon_id',
    ];

    protected $with = ['attachments', 'customer', 'user:id,name,username', 'deliveries:id,sale_id'];

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function costings()
    {
        return $this->hasMany(Costing::class);
    }

    public function del()
    {
        if ($this->payments()->received()->exists() || $this->directPayments()->received()->exists()) {
            return false;
        }

        return $this->delete();
    }

    public function deliveries()
    {
        return $this->hasMany(Delivery::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class)->orderBy('order');
    }

    public function payable()
    {
        return $this->customer();
    }

    public function registerRecord()
    {
        return $this->belongsTo(RegisterRecord::class);
    }

    public function routeNotificationForMail($notification)
    {
        return $this->customer->email;
    }

    public static function scopeNonPos($query)
    {
        return $query->whereNull('pos')->orWhere('pos', 0);
    }

    public static function scopePos($query)
    {
        return $query->where('pos', 1);
    }

    public static function scopeShop($query)
    {
        return $query->where('shop', 1);
    }

    public static function scopeValid($query)
    {
        return $query->whereNull('void')->orWhere('void', 0);
    }

    public static function scopeVoid($query)
    {
        return $query->where('void', 1);
    }

    public function shopUrl()
    {
        return auth()->user() ? route('shop.order', ['order' => $this->id]) : URL::signedRoute('shop.order.view', ['order' => $this->id]);
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
        static::deleting(function ($sale) {
            event(new SaleEvent($sale, 'deleting'));
        });
        static::deleted(function ($sale) {
            $sale->taxes()->detach();
            $sale->payments()->detach();
            $sale->items->each->delete();
            $sale->directPayments()->delete();
        });
    }
}
