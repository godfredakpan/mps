<?php

namespace Modules\MPS\Models;

use Modules\MPS\Models\Traits\HasTaxes;
use Illuminate\Database\Eloquent\Builder;
use Modules\MPS\Models\Traits\OfLocation;
use Modules\MPS\Models\Traits\HasCategories;
use Modules\MPS\Models\Traits\HasAttachments;
use Modules\MPS\Models\Traits\HasSchemalessAttributes;

class Expense extends Base
{
    use HasAttachments;
    use HasCategories;
    use HasSchemalessAttributes;
    use HasTaxes;
    use OfLocation;

    public $hasLocation = true;

    public $hasNumber = true;

    public $hasReference = true;

    public static $searchable = [
        'id', 'date', 'created_at', 'title', 'reference', 'amount', 'approved', 'approved_by.name',
        'user.name', 'details', 'extra_attributes', 'number', 'recurring', 'start_date', 'repeat',
    ];

    protected $casts = ['date' => 'date', 'extra_attributes' => 'array'];

    protected $dates = ['last_created_at', 'start_date'];

    protected $fillable = [
        'date', 'title', 'amount', 'reference', 'details', 'account_id', 'user_id', 'approved', 'approved_by_id', 'extra_attributes',
        'recurring', 'expense_id', 'start_date', 'repeat', 'create_before', 'last_created_at',
    ];

    protected $hidden = ['updated_at'];

    protected $with = ['attachments'];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    public function del()
    {
        return $this->delete();
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public static function scopeApproved($query)
    {
        return $query->where('approved', 1);
    }

    public static function scopeNotApproved($query)
    {
        return $query->whereNULL('approved')->orWhere('approved', 0);
    }

    public static function scopeRecurring($query)
    {
        return $query->where('recurring', 1);
    }

    public static function scopeRequireApproval($query, $user_id = null)
    {
        return $query->notApproved()->where('approved_by_id', $user_id ?: auth()->id());
    }

    public function user()
    {
        return $this->belongsTo(User::class);
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
        static::deleting(function ($expense) {
            $expense->categories()->detach();
        });
    }
}
