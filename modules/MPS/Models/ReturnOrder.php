<?php

namespace Modules\MPS\Models;

use Modules\MPS\Models\Traits\HasTaxes;
use Modules\MPS\Events\ReturnOrderEvent;
use Illuminate\Database\Eloquent\Builder;
use Modules\MPS\Models\Traits\OfLocation;
use Modules\MPS\Models\Traits\BelongsToUser;
use Modules\MPS\Models\Traits\DirectPayments;
use Modules\MPS\Models\Traits\HasAttachments;
use Modules\MPS\Models\Traits\HasManySyncable;
use Modules\MPS\Models\Traits\BelongsToCustomer;
use Modules\MPS\Models\Traits\BelongsToLocation;
use Modules\MPS\Models\Traits\BelongsToPayments;
use Modules\MPS\Models\Traits\BelongsToSupplier;
use Modules\MPS\Models\Traits\HasSchemalessAttributes;

class ReturnOrder extends Base
{
    use BelongsToCustomer;
    use BelongsToLocation;
    use BelongsToPayments;
    use BelongsToSupplier;
    use BelongsToUser;
    use DirectPayments;
    use HasAttachments;
    use HasManySyncable;
    use HasSchemalessAttributes;
    use HasTaxes;
    use OfLocation;

    public $hasHash = true;

    public $hasLocation = true;

    public $hasNumber = true;

    public $hasReference = true;

    public $hasUser = true;

    public static $searchable = [
        'id', 'date', 'reference', 'customer.name', 'customer.company',  'supplier.name', 'supplier.company',
        'user.name', 'type', 'discount', 'total', 'grand_total', 'details', 'transaction_id', 'extra_attributes', 'number',
    ];

    protected $casts = ['date' => 'date', 'extra_attributes' => 'array'];

    protected $fillable = [
        'date', 'type', 'reference', 'discount', 'discount_amount', 'total_tax_amount', 'order_tax_amount',
        'item_tax_amount', 'recoverable_tax_amount', 'recoverable_tax_calculated_on', 'shipping', 'total', 'grand_total',
        'details', 'hash', 'deduct_from_register', 'customer_id', 'supplier_id', 'location_id', 'user_id', 'transaction_id', 'extra_attributes',
    ];

    protected $with = ['attachments', 'customer', 'supplier', 'user:id,name,username'];

    public function del()
    {
        // TODO
        // if ($this->products()->exists() || $this->invoices()->exists() || $this->purchases()->exists()) {
        //     return false;
        // }

        return $this->delete();
    }

    public function items()
    {
        return $this->hasMany(ReturnOrderItem::class)->orderBy('created_at', 'asc');
    }

    public function routeNotificationForMail($notification)
    {
        return $this->customer_id ? $this->customer->email : $this->supplier->email;
    }

    public function withAll()
    {
        return $this->loadMissing([
            'items' => function ($query) {
                $query->withAll();
            },
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
        static::addGlobalScope('of_location', function (Builder $builder) {
            if ($location_id = session('location_id')) {
                return $builder->where('location_id', $location_id);
            }
        });
        static::deleting(function ($r) {
            event(new ReturnOrderEvent($r, 'deleting'));
        });
        static::deleted(function ($r) {
            $r->taxes()->detach();
            $r->items()->delete();
            $r->payments()->detach();
            $r->directPayments()->delete();
        });
    }
}
