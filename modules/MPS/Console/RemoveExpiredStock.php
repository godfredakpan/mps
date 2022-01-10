<?php

namespace Modules\MPS\Console;

use Illuminate\Console\Command;
use Modules\MPS\Models\PurchaseItem;

class RemoveExpiredStock extends Command
{
    protected $description = 'Remove expired stock';

    protected $signature = 'stock:expired';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $purchaseItems = PurchaseItem::available()->notExpired()->whereNotNull('expiry_date')->where('expiry_date', '<', now())->get();

        if ($purchaseItems->isEmpty()) {
            $this->info(__('No item is expiring today or item stock is already adjusted.'));
        } else {
            foreach ($purchaseItems as $pi) {
                $this->info(__choice(
                    'Item :item stock (:stock) is expired.',
                    ['item' => $pi->name . ' (' . $pi->code . ')', 'stock' => formatQuantity($pi->balance)]
                ));
                $stock = $pi->stock->first();
                if ($stock) {
                    $stock->update(['quantity' => $stock->quantity - $pi->balance]);
                    // activity()->performedOn($stock)->withProperties($stock)->log(__('Stock has been expired.'));
                }
                $pi->update(['balance' => 0, 'expired_quantity' => $pi->balance]);
                // activity()->withProperties($pi->item)->log(__('Item stock has been expired.'));
            }
        }
    }
}
