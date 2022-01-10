<?php

namespace Modules\MPS\Models;

use Modules\MPS\Models\Traits\HasStock;
use Modules\MPS\Models\Traits\HasTaxes;
use Modules\MPS\Models\Traits\BelongsToItem;
use Modules\MPS\Models\Traits\OrderItemTrait;
use Modules\MPS\Models\Traits\HasManySyncable;

class PurchaseItem extends Base
{
    use BelongsToItem;
    use HasManySyncable;
    use HasStock;
    use HasTaxes;
    use OrderItemTrait;

    public $hasLocation = true;

    public static $searchable = [
        'id', 'code', 'name', 'expiry_date', 'balance',
    ];

    protected $casts = ['item_taxes' => 'object'];

    protected $fillable = [
        'name', 'code', 'quantity', 'cost', 'unit_cost', 'subtotal', 'item_taxes', 'unit_id', 'order',
        'item_id', 'tax_amount', 'total_tax_amount', 'net_cost', 'discount', 'batch_no', 'expiry_date',
        'discount_amount', 'total_discount_amount', 'balance', 'base_net_cost', 'base_unit_cost', 'expired_quantity',
    ];

    protected $with = ['taxes'];

    public function createSerials($pId, $serials)
    {
        if ($pId && !empty($serials)) {
            foreach ($serials as $serial) {
                Serial::updateOrCreate(
                    ['number' => $serial],
                    ['number' => $serial, 'purchase_id' => $pId, 'purchase_item_id' => $this->id, 'item_id' => $this->item_id]
                );
            }
        }
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function scopeAvailable($query)
    {
        $query->whereNotNull('balance')->where('balance', '>', 0);
    }

    public function scopeExpired($query)
    {
        $query->whereNotNull('expired_quantity');
    }

    public function scopeNotExpired($query)
    {
        $query->whereNull('expired_quantity');
    }

    public function scopeSold($query)
    {
        $query->whereNull('balance')->orWhere('balance', '<=', 0);
    }

    public function scopeWithAll($query)
    {
        $query->with(['item' => function ($q) {
            $q->withAll();
        }, 'variations']);
    }

    public function scopeWithSelected($query)
    {
        $query->with(['item', 'variations']);
    }

    public function serials()
    {
        return $this->hasMany(Serial::class);
    }

    public function stock($location_id = null)
    {
        return $this->hasMany(Stock::class, 'item_id', 'item_id')->ofLocation($location_id ? $location_id : $this->location_id);
    }

    public function variations()
    {
        return $this->belongsToMany(Variation::class)
            ->withPivot(
                'cost',
                'quantity',
                'net_cost',
                'tax_amount',
                'discount_amount',
                'total_tax_amount',
                'total_discount_amount',
                'total'
            );
    }
}
