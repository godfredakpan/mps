<?php

namespace Modules\MPS\Models;

use Modules\MPS\Models\Traits\HasTaxes;
use Illuminate\Database\Eloquent\Builder;
use Modules\MPS\Models\Traits\OrderTrait;
use Modules\MPS\Events\StockAdjustmentEvent;
use Modules\MPS\Models\Traits\BelongsToUser;
use Modules\MPS\Models\Traits\HasAttachments;
use Modules\MPS\Models\Traits\HasManySyncable;
use Modules\MPS\Models\Traits\BelongsToLocation;

class StockAdjustment extends Base
{
    use BelongsToLocation;
    use BelongsToUser;
    use HasAttachments;
    use HasManySyncable;
    use HasTaxes;
    use OrderTrait;

    public $hasLocation = true;

    public $hasNumber = true;

    public $hasReference = true;

    public $hasUser = true;

    public static $searchable = [
        'id', 'date', 'type', 'reference', 'status', 'draft', 'discount', 'total',
        'grand_total', 'details', 'location.name', 'user.name', 'extra_attributes', 'number',
    ];

    protected $casts = ['date' => 'date', 'extra_attributes' => 'array'];

    protected $fillable = [
        'date', 'type', 'reference', 'discount', 'status', 'discount_amount', 'total_tax_amount',
        'order_tax_amount', 'item_tax_amount', 'recoverable_tax_amount', 'recoverable_tax_calculated_on',
        'total', 'grand_total', 'details',  'draft', 'location_id', 'user_id', 'extra_attributes',
    ];

    protected $with = ['attachments', 'location', 'user'];

    public function del()
    {
        return $this->delete();
    }

    public function items()
    {
        return $this->hasMany(StockAdjustmentItem::class);
    }

    public function withItems()
    {
        return $this->loadMissing([
            'items' => fn ($q) => $q->withAdjustment($this->from),
        ]);
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
        static::deleting(function ($stock_adjustment) {
            event(new StockAdjustmentEvent($stock_adjustment, 'deleting'));
        });
        static::deleted(function ($stock_adjustment) {
            $stock_adjustment->taxes()->detach();
            $stock_adjustment->items->each->delete();
        });
    }
}
