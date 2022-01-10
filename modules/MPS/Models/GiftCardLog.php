<?php

namespace Modules\MPS\Models;

class GiftCardLog extends Base
{
    public static $searchable = ['id', 'amount', 'description', 'giftCard.number'];

    protected $fillable = ['amount', 'description', 'gift_card_id', 'payment_id'];

    public function giftCard()
    {
        return $this->belongsTo(GiftCard::class);
    }
}
