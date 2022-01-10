<?php

namespace Modules\MPS\Models;

use Modules\MPS\Models\Traits\HasStock;
use Modules\MPS\Models\Traits\HasTaxes;
use Modules\MPS\Models\Traits\BelongsToItem;
use Modules\MPS\Models\Traits\HasPromotions;
use Modules\MPS\Models\Traits\OrderItemTrait;
use Modules\MPS\Models\Traits\HasManySyncable;

class RecurringSaleItem extends Base
{
    use BelongsToItem;
    use HasManySyncable;
    use HasPromotions;
    use HasStock;
    use HasTaxes;
    use OrderItemTrait;

    protected $casts = ['item_taxes' => 'object'];

    protected $fillable = [
        'name', 'code', 'quantity', 'price', 'unit_price', 'subtotal', 'item_taxes',
        'item_id', 'tax_amount', 'total_tax_amount', 'net_price', 'discount', 'order',
        'discount_amount', 'total_discount_amount', 'unit_id', 'batch_no', 'expiry_date',
    ];

    protected $with = ['stock', 'taxes', 'promotions'];

    public function getItemTaxesAttribute($value)
    {
        return json_decode($value);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function recurringSale()
    {
        return $this->belongsTo(RecurringSale::class);
    }

    public static function scopeComingSoon($query)
    {
        return $query->whereNull('received')->orWhereReceived(0);
    }

    public function setItemTaxesAttribute($value)
    {
        $this->attributes['item_taxes'] = json_encode($value);
    }
}
