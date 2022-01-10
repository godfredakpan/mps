<?php

namespace Modules\MPS\Models;

use Illuminate\Database\Eloquent\Builder;

class AssetTransfer extends Base
{
    public $hasNumber = true;

    public $hasReference = true;

    public $hasUser = true;

    public static $searchable = ['id', 'amount', 'details', 'number'];

    protected $guarded = [];

    protected $with = ['fromAccount', 'toAccount'];

    public function del()
    {
        return $this->delete();
    }

    public function fromAccount()
    {
        return $this->belongsTo(Account::class, 'from');
    }

    public function toAccount()
    {
        return $this->belongsTo(Account::class, 'to');
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
