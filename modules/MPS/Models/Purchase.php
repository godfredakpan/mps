<?php

namespace Modules\MPS\Models;

use Modules\MPS\Events\PurchaseEvent;
use Modules\MPS\Models\Traits\HasTaxes;
use Illuminate\Database\Eloquent\Builder;
use Modules\MPS\Models\Traits\OfLocation;
use Modules\MPS\Models\Traits\OrderTrait;
use Modules\MPS\Models\Traits\BelongsToUser;
use Modules\MPS\Models\Traits\DirectPayments;
use Modules\MPS\Models\Traits\HasAttachments;
use Modules\MPS\Models\Traits\HasManySyncable;
use Modules\MPS\Models\Traits\BelongsToLocation;
use Modules\MPS\Models\Traits\BelongsToPayments;
use Modules\MPS\Models\Traits\BelongsToSupplier;
use Modules\MPS\Models\Traits\HasSchemalessAttributes;

class Purchase extends Base
{
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
    use OrderTrait;

    public $hasHash = true;

    public $hasLocation = true;

    public $hasNumber = true;

    public $hasReference = true;

    public $hasUser = true;

    public static $searchable = [
        'id', 'date', 'reference', 'supplier.name', 'supplier.company', 'user.name', 'number',
        'type', 'discount', 'total', 'grand_total', 'details', 'transaction_id', 'extra_attributes',
    ];

    protected $casts = ['date' => 'date', 'extra_attributes' => 'array'];

    protected $fillable = [
        'date', 'type', 'reference', 'discount', 'discount_amount', 'total_tax_amount', 'order_tax_amount',
        'item_tax_amount', 'recoverable_tax_amount', 'recoverable_tax_calculated_on', 'shipping', 'total', 'grand_total',
        'details', 'hash', 'paid', 'draft', 'supplier_id', 'location_id', 'user_id', 'transaction_id', 'extra_attributes',
    ];

    protected $with = ['attachments', 'supplier', 'user:id,name,username'];

    public function del()
    {
        if ($this->payments()->exists() || $this->directPayments()->exists()) {
            return false;
        }

        return $this->delete();
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class)->orderBy('order');
    }

    public function payable()
    {
        return $this->supplier();
    }

    public function routeNotificationForMail($notification)
    {
        return $this->supplier->email;
    }

    public static function scopeValid($query)
    {
        return $query->whereNull('void')->orWhere('void', 0);
    }

    public static function scopeVoid($query)
    {
        return $query->where('void', 1);
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
        static::deleting(function ($purchase) {
            event(new PurchaseEvent($purchase, 'deleting'));
        });
        static::deleted(function ($purchase) {
            $purchase->taxes()->detach();
            $purchase->items()->delete();
            $purchase->payments()->detach();
            $purchase->directPayments()->delete();
        });
    }
}
