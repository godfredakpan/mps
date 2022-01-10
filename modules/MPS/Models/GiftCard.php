<?php

namespace Modules\MPS\Models;

class GiftCard extends Base
{
    public static $searchable = ['id', 'number', 'amount', 'points',  'balance', 'expiry_date', 'details', 'customer.name', 'customer.company'];

    protected $fillable = ['number', 'amount', 'points',  'balance', 'expiry_date', 'customer_id', 'details'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function del()
    {
        if ($this->logs()->exists()) {
            return false;
        }
        return $this->delete();
    }

    public function log($data)
    {
        $this->logs()->create(['amount' => $data['amount'], 'description' => $data['description']]);
    }

    public function logs()
    {
        return $this->hasMany(GiftCardLog::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'gift_card_number', 'number');
    }

    public static function scopeSearch($query, $search)
    {
        if (!empty($search)) {
            $query->where(function ($query) use ($search) {
                $query->where('number', 'like', "%{$search}%");
            });
        }
        return $query;
    }

    public function updateBalance($amount)
    {
        $temp = $this->getEventDispatcher();
        $this->unsetEventDispatcher();
        $this->update(['balance' => $amount]);
        $this->setEventDispatcher($temp);
    }
}
