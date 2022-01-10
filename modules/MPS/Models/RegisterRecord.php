<?php

namespace Modules\MPS\Models;

use Illuminate\Database\Eloquent\Builder;

class RegisterRecord extends Base
{
    public $hasLocation = true;

    public static $searchable = [
        'cash_in_hand', 'closed_by', 'closed_at', 'transferred_to', 'comment',
        'location_id', 'register.name', 'user.name', 'total_cash_amount', 'total_cash_submitted', 'total_cheques',
        'total_cheques_amount', 'total_cheques_submitted', 'total_cc_slips', 'total_cc_slips_amount', 'total_cc_slips_submitted',
        'total_other_amount', 'total_refunds_amount', 'total_expenses_amount', 'total_gift_card_amount', 'total_return_orders_amount',
    ];

    protected $dates = ['closed_at'];

    protected $fillable = [
        'cash_in_hand', 'closed_by', 'closed_at', 'transferred_to', 'comment',
        'location_id', 'register_id', 'user_id', 'total_cash_amount', 'total_cash_submitted', 'total_cheques',
        'total_cheques_amount', 'total_cheques_submitted', 'total_cc_slips', 'total_cc_slips_amount', 'total_cc_slips_submitted',
        'total_other_amount', 'total_refunds_amount', 'total_expenses_amount', 'total_gift_card_amount', 'total_return_orders_amount',
    ];

    public function closedByUser()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function del()
    {
        return false;
        // return $this->delete();
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function payments()
    {
        return $this->hasManyThrough(Payment::class, Sale::class);
    }

    public function register()
    {
        return $this->belongsTo(Register::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public static function scopeClosed($query)
    {
        return $query->whereNotNull('closed_at');
    }

    public static function scopeMine($query)
    {
        return $query->where('user_id', auth()->id());
    }

    public static function scopeNotClosed($query)
    {
        return $query->whereNull('closed_at');
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
        // static::addGlobalScope('of_location', function (Builder $builder) {
        //     if ($location_id = session('location_id')) {
        //         return $builder->where('location_id', $location_id);
        //     }
        // });
    }
}
