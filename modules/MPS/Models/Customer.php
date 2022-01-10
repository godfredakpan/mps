<?php

namespace Modules\MPS\Models;

use App\Helpers\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Modules\MPS\Models\Traits\AccountingJournal;
use Modules\MPS\Models\Traits\HasSchemalessAttributes;

class Customer extends Base
{
    use AccountingJournal;
    use HasSchemalessAttributes;
    use Notifiable;

    public static $searchable = [
        'id', 'name', 'company', 'email', 'phone', 'address', 'state', 'country', 'state_name', 'country_name',
        'due_limit', 'extra_attributes', 'points', 'house_no', 'street_no', 'city', 'postal_code',
    ];

    protected $casts = ['extra_attributes' => 'array'];

    protected $fillable = [
        'name', 'company', 'email', 'phone', 'user_id', 'customer_group_id',  'max_due_amount', 'points',
        'due_limit', 'opening_balance', 'address', 'state', 'country', 'state_name', 'country_name', 'extra_attributes',
        'house_no', 'street_no', 'city', 'postal_code'
    ];

    protected $hidden = ['created_at', 'updated_at'];

    protected $with = ['customerGroup'];

    public function customerGroup()
    {
        return $this->belongsTo(CustomerGroup::class);
    }

    public function del()
    {
        if ($this->sales()->exists() || $this->payments()->exists()) {
            return false;
        }

        return $this->delete();
    }

    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public static function scopeSearch($query, $search)
    {
        if (!empty($search)) {
            $query->where(function ($query) use ($search) {
                $query->where('id', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%");
            });
        }
        return $query;
    }

    public function users()
    {
        return $this->hasMany(User::class);
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

        static::creating(function ($customer) {
            if (!$customer->opening_balance) {
                $customer->opening_balance = 0;
            }
        });

        static::created(function ($customer) {
            $customer->initJournal();
            $customer->refresh()->journal->creditDollars($customer->opening_balance ?? 0, 'opening_balance');
        });
    }
}
