<?php

namespace Modules\MPS\Observers;

use Modules\MPS\Models\Customer;
use Modules\MPS\Models\GiftCard;

class GiftCardObserver
{
    public function created(GiftCard $card)
    {
        $card->log([
            'amount'      => $card->amount,
            'description' => __('gift_card_created'),
        ]);
        if ($card->points && $card->points > 0 && $card->customer_id) {
            $card->customer->update(['points' => $card->customer->points - $card->points]);
        }
    }

    public function deleted(GiftCard $card)
    {
        $card->logs()->delete();
        if ($card->points && $card->points > 0 && $card->customer_id) {
            $card->customer->update(['points' => $card->customer->points + $card->points]);
        }
    }

    // public function deleting(GiftCard $card) {}

    public function updating(GiftCard $card)
    {
        if (!$card->wasRecentlyCreated) {
            $card->log([
                'amount'      => $card->amount,
                'description' => __('gift_card_updated', [
                    'new_amount'  => formatNumber($card->amount),
                    'new_balance' => formatNumber($card->balance),
                    'old_amount'  => formatNumber($card->getOriginal('amount')),
                    'old_balance' => formatNumber($card->getOriginal('balance')),
                ]),
            ]);
            if (!$card->isDirty('points') || $card->isDirty('customer_id')) {
                if ($card->getOriginal('points') && $card->getOriginal('points') > 0 && $card->getOriginal('customer_id')) {
                    $customer = Customer::find($card->getOriginal('customer_id'));
                    $customer->update(['points' => $card->customer->points + $card->getOriginal('points')]);
                }
                if ($card->points && $card->points > 0 && $card->customer_id) {
                    $customer = Customer::find($card->customer_id);
                    $customer->update(['points' => $card->customer->points - $card->points]);
                }
            }
        }
    }
}
