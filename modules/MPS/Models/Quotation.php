<?php

namespace Modules\MPS\Models;

use Modules\MPS\Models\Traits\HasTaxes;
use Illuminate\Database\Eloquent\Builder;
use Modules\MPS\Models\Traits\OfLocation;
use Modules\MPS\Models\Traits\OrderTrait;
use Modules\MPS\Models\Traits\BelongsToUser;
use Modules\MPS\Models\Traits\HasAttachments;
use Modules\MPS\Models\Traits\HasManySyncable;
use Modules\MPS\Models\Traits\BelongsToCustomer;
use Modules\MPS\Models\Traits\BelongsToLocation;
use Modules\MPS\Models\Traits\HasSchemalessAttributes;

class Quotation extends Base
{
    use BelongsToCustomer;
    use BelongsToLocation;
    use BelongsToUser;
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
        'id', 'date', 'reference', 'customer.name', 'customer.company', 'user.name',
        'discount', 'total', 'grand_total', 'details', 'transaction_id', 'extra_attributes', 'number',
    ];

    protected $casts = ['date' => 'date', 'extra_attributes' => 'array'];

    protected $fillable = [
        'date', 'reference', 'discount', 'discount_amount', 'total_tax_amount', 'order_tax_amount',
        'item_tax_amount', 'recoverable_tax_amount', 'recoverable_tax_calculated_on', 'shipping', 'total', 'grand_total',
        'details', 'hash', 'customer_id', 'location_id', 'user_id', 'transaction_id', 'extra_attributes',
    ];

    protected $with = ['attachments', 'customer', 'user:id,name,username'];

    public function del()
    {
        return $this->delete();
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class)->orderBy('order');
    }

    public function routeNotificationForMail($notification)
    {
        return $this->customer->email;
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

        static::deleted(function ($sale) {
            $sale->taxes()->detach();
            $sale->items()->delete();
        });
    }
}
