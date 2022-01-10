<?php

namespace Modules\MPS\Models;

use Modules\MPS\Models\Traits\AccountingJournal;

class Account extends Base
{
    use AccountingJournal;

    public static $searchable = ['id', 'name', 'type', 'reference', 'details'];

    protected $fillable = ['name', 'type', 'reference', 'details', 'opening_balance', 'fees', 'fixed', 'percentage', 'apply_to'];

    protected $hidden = ['created_at', 'updated_at'];

    public function del()
    {
        if (($this->journal && $this->journal->transactions()->exists()) || $this->payments()->exists() || $this->expenses()->exists() || $this->incomes()->exists() || $this->toTransfers()->exists() || $this->fromTransfers()->exists()) {
            return false;
        }

        return $this->delete();
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function fromTransfers()
    {
        return $this->hasMany(Transfer::class, 'from');
    }

    public function incomes()
    {
        return $this->hasMany(Income::class);
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public static function scopeOffline($query)
    {
        return $query->where('offline', 1);
    }

    public static function scopeSearch($query, $search)
    {
        if (!empty($search)) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            });
        }
        return $query;
    }

    public function toTransfers()
    {
        return $this->hasMany(Transfer::class, 'to');
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($account) {
            $account->initJournal();
            if ($account->opening_balance) {
                $account->refresh()->journal->creditDollars($account->opening_balance, 'opening_balance');
            }
        });
    }
}
