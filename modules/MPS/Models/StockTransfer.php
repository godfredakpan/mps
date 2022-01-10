<?php

namespace Modules\MPS\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Modules\MPS\Events\StockTransferEvent;
use Modules\MPS\Models\Traits\HasAttachments;
use Modules\MPS\Models\Traits\HasManySyncable;

class StockTransfer extends Base
{
    use HasAttachments;
    use HasManySyncable;

    public $hasNumber = true;

    public $hasReference = true;

    public $hasUser = true;

    public static $searchable = ['id', 'to', 'from', 'details', 'status', 'reference', 'number'];

    protected $casts = ['date' => 'date', 'extra_attributes' => 'array'];

    protected $fillable = ['to', 'from', 'details', 'status', 'reference'];

    protected $with = ['attachments', 'fromLocation', 'toLocation', 'user'];

    public function del()
    {
        return $this->delete();
    }

    public function fromLocation()
    {
        return $this->belongsTo(Location::class, 'from');
    }

    public function items()
    {
        return $this->hasMany(StockTransferItem::class);
    }

    public function saveTransfer(array $data, $update = false)
    {
        $transfer = $this;
        return DB::transaction(function () use ($data, $transfer, $update) {
            if ($update) {
                $transfer->update($data);
            } else {
                $transfer = StockTransfer::create($data);
            }
            $relation_children = [];
            $data['items'] = $this->flatVariations($data['items']);
            $relation_children[] = ['field' => 'id', 'relation' => 'serials', 'sync' => true, 'assoc' => false];
            $relation_children[] = ['field' => 'id', 'relation' => 'variations', 'sync' => true, 'assoc' => false];
            $items = $transfer->syncHasMany($data['items'], 'items', 'id', true, $relation_children);

            return $transfer;
        });
    }

    public function toLocation()
    {
        return $this->belongsTo(Location::class, 'to');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function withItems()
    {
        return $this->loadMissing([
            'items' => fn ($q) => $q->withTransfer($this->from),
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
        static::deleting(function ($stock_transfer) {
            event(new StockTransferEvent($stock_transfer, 'deleting'));
        });
        static::deleted(function ($stock_transfer) {
            $stock_transfer->items()->delete();
        });
    }

    private function flatVariations($items)
    {
        foreach ($items as &$item) {
            $variations = [];
            if (!empty($item['variations'])) {
                foreach ($item['variations'] as $variation) {
                    foreach ($variation as $k => $v) {
                        $variations[$k] = $v;
                    }
                }
            }
            $item['variations'] = $variations;
        }
        return $items;
    }
}
