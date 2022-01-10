<?php

namespace Modules\MPS\Models;

use Illuminate\Database\Eloquent\Builder;
use Modules\MPS\Models\Traits\OfLocation;
use Modules\MPS\Models\Traits\HasAttachments;

// use Modules\MPS\Models\Traits\HasSchemalessAttributes;

class Payment extends Base
{
    use HasAttachments;
    // use HasSchemalessAttributes;
    use OfLocation;

    public $hasHash = true;

    public $hasLocation = true;

    public $hasNumber = true;

    public $hasReference = true;

    public $hasUser = true;

    public static $searchable = [
        'id', 'created_at', 'number',
        'card_holder', 'card_number', 'cheque_number', 'gift_card_number',
        'account.name', 'user.name', 'payable.name.morph', 'payable.company.morph',
        'reference', 'amount', 'details', 'gateway', 'received', 'hash', 'file', 'review', 'reviewed_by',
        'location_id', 'sale_id', 'purchase_id', 'user_id', 'account_id', 'payable_id', 'payable_type',
        'payer_transaction_id', 'account_transaction_id', 'gateway_transaction_id', 'extra_attributes',
    ];

    protected $casts = ['date' => 'date'];

    protected $fillable = [
        'reference', 'amount', 'details', 'gateway', 'received', 'hash', 'file', 'review', 'reviewed_by',
        'location_id', 'sale_id', 'purchase_id', 'user_id', 'account_id', 'payable_id', 'payable_type',
        'payer_transaction_id', 'account_transaction_id', 'gateway_transaction_id', 'extra_attributes',
        'return_id', 'card_holder', 'card_number', 'expiry_date', 'cheque_number', 'gift_card_number',
    ];

    protected $hidden = ['updated_at'];

    protected $with = ['attachments', 'user', 'account', 'payable'];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function giftCard()
    {
        return $this->belongsTo(GiftCard::class, 'gift_card_number', 'number');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function payable()
    {
        return $this->morphTo();
    }

    public function purchases()
    {
        return $this->belongsToMany(Purchase::class);
    }

    public function returnOrders()
    {
        return $this->belongsToMany(ReturnOrder::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function sales()
    {
        return $this->belongsToMany(Sale::class);
    }

    public static function scopeDue($query)
    {
        return $query->whereNull('received')->orWhere('received', 0)->whereNull('review');
    }

    public static function scopeMyPayments($query)
    {
        $user = auth()->user();
        if (!$user->hasRole(['admin', 'super'])) {
            if ($user->hasRole('customer')) {
                return $query->where('payable_id', $user->customer_id)->where('payable_type', 'Modules\MPS\Models\Customer');
            } elseif ($user->hasRole('vendor')) {
                return $query->where('payable_id', $user->vendor_id)->where('payable_type', 'Modules\MPS\Models\Vendor');
            }
            return $query->where('user_id', $user->id);
        }
    }

    public static function scopeOfLocation($query, $location_id = null)
    {
        if (!$location_id) {
            $location_id = session('location_id');
        }

        return $query->where('location_id', $location_id);
    }

    public static function scopeReceived($query)
    {
        return $query->where('received', 1);
    }

    public static function scopeReview($query)
    {
        return $query->where('review', 1)->whereNull('reviewed_by');
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
                if ($user->hasRole('customer')) {
                    return $builder->where('payable_id', $user->customer_id)->where('payable_type', 'Modules\MPS\Models\Customer');
                } elseif ($user->hasRole('vendor')) {
                    return $builder->where('payable_id', $user->vendor_id)->where('payable_type', 'Modules\MPS\Models\Vendor');
                }
                return $builder->where('user_id', $user->id);
            }
        });
        // static::addGlobalScope('of_location', function (Builder $builder) {
        //     if ($location_id = session('location_id')) {
        //         return $builder->where('payments.location_id', $location_id);
        //     }
        // });
        static::deleting(function ($payments) {
            $payments->sales()->detach();
            $payments->purchases()->detach();
        });
    }
}
