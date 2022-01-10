<?php

namespace Modules\MPS\Models;

use Illuminate\Database\Eloquent\Builder;
use Modules\MPS\Models\Traits\HasAttachments;

class Salary extends Base
{
    use HasAttachments;

    public $hasReference = true;

    public static $searchable = [
        'id', 'date', 'type', 'reference', 'advance', 'user_id', 'account_id',
        'amount', 'details', 'status', 'work_hours', 'work_hours_salary', 'points',
    ];

    protected $fillable = [
        'date', 'type', 'reference', 'advance', 'user_id', 'account_id',
        'amount', 'details', 'status', 'work_hours', 'work_hours_salary', 'points',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public static function scopeAdvance($query)
    {
        return $query->where('advance', 1);
    }

    public static function scopeCommission($query)
    {
        return $query->where('type', 'commission');
    }

    public static function scopeNotAdvance($query)
    {
        return $query->whereNull('advance')->orWhere('advance', 0);
    }

    public static function scopeSalary($query)
    {
        return $query->where('type', 'salary');
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
    }
}
