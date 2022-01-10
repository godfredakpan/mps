<?php

namespace Modules\MPS\Models;

class Promotion extends Base
{
    public static $searchable = [
        'id', 'created_at', 'updated_at',
        'name', 'type', 'start_date', 'end_date', 'active', 'discount', 'discount_method', 'show_on_receipt', 'details',
        'amount_to_spend', 'item_id_to_buy', 'quantity_to_buy', 'item_id_to_get', 'quantity_to_get', 'times_to_apply', 'coupon_code',
    ];

    protected $fillable = [
        'name', 'type', 'start_date', 'end_date', 'active', 'discount', 'discount_method', 'show_on_receipt', 'details',
        'amount_to_spend', 'item_id_to_buy', 'quantity_to_buy', 'item_id_to_get', 'quantity_to_get', 'times_to_apply', 'coupon_code',
    ];

    protected $with = ['itemToBuy', 'itemToGet'];

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function items()
    {
        return $this->belongsToMany(Item::class);
    }

    public function itemToBuy()
    {
        return $this->belongsTo(Item::class, 'item_id_to_buy');
    }

    public function itemToGet()
    {
        return $this->belongsTo(Item::class, 'item_id_to_get');
    }

    public static function scopeValid($query, $date = null)
    {
        $today = $date ?? now();
        $query->where('active', 1);
        $query->whereNull('end_date')->orWhereDate('end_date', '>=', $today);
        $query->whereNull('start_date')->orWhereDate('start_date', '<=', $today);
        return $query;
    }
}
