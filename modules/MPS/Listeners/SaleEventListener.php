<?php

namespace Modules\MPS\Listeners;

use Modules\MPS\Models\Sale;
use Modules\MPS\Events\SaleEvent;
use Illuminate\Support\Facades\Log;

class SaleEventListener
{
    public $sale;

    public function failed(SaleEvent $event, $exception)
    {
        Log::error('SaleEventListener failed!', [
            'Error' => $exception,
            'sale'  => $event->sale,
        ]);
    }

    public function handle(SaleEvent $event)
    {
        $this->{$event->method}($event);
    }

    // private function addStockTrail(SaleItem $item, $quantity, $location, $type)
    // {
    //     $base_quantity = convert_to_base_quantity($quantity, $item->unit);
    //     $item->stockTrails()->create([
    //         'location_id'   => $location,
    //         'quantity'      => $quantity,
    //         'base_quantity' => $base_quantity,
    //         'item_id'       => $item->item_id,
    //         'unit_id'       => $item->unit_id,
    //         'type'          => 'sale_item_' . $type,
    //     ]);
    // }

    private function completed($original_sale, $type)
    {
        $ajt = $this->sale->customer->journal
                ->creditDollars($this->sale->grand_total, 'sale_' . $type)
                ->referencesObject($this->sale);
        $this->sale->disableLogging();
        $this->sale->transaction_id = $ajt->id;
        $this->sale->saveQuietly();
        // TODO: mark sale paid check if customer has balance
        if (!$original_sale || $original_sale->draft) {
            if (safe_email($this->sale->customer->email) && !default_customer($this->sale->customer_id)) {
                $this->sale->customer->notify(new \Modules\MPS\Notifications\SaleCreated($this->sale));
            } elseif ($this->sale->shop && $this->sale->address && safe_email($this->sale->address->email)) {
                $this->sale->address->notify(new \Modules\MPS\Notifications\SaleCreated($this->sale));
            }
        }

        // $this->sale = $this->sale->fresh();
        foreach ($this->sale->items as $item) {
            if ($item->item->type == 'standard' || $item->item->type == 'service') {
                $stock         = $item->stock->isNotEmpty() ? $item->stock->first() : null;
                $base_quantity = convert_to_base_quantity($item->quantity, $item->unit);
                if ($stock) {
                    $stock->update(['quantity' => $stock->quantity - $base_quantity]);
                } else {
                    $item->stock()->create(['quantity' => 0 - $base_quantity, 'location_id' => $this->sale->location_id]);
                }
            } elseif ($item->item->type == 'combo' || $item->item->type == 'recipe') {
                foreach ($item->portions as $portion) {
                    if ($portion->essentials) {
                        foreach ($portion->essentials as $essential) {
                            $equantity = $essential->quantity * ($portion->pivot ? $portion->pivot->quantity : 1);
                            if ($essential->item->type == 'standard' || $essential->item->type == 'service') {
                                $stock = $essential->item->locationStock()->exists() ? $essential->item->locationStock()->first() : null;
                                // $base_quantity = convert_to_base_quantity($item->quantity, $item->unit);
                                if ($stock) {
                                    $stock->update(['quantity' => $stock->quantity - $equantity]);
                                } else {
                                    $essential->item->stock()->create(['quantity' => 0 - $equantity, 'location_id' => $this->sale->location_id]);
                                }
                                if ($essential->item->has_variants && $essential->variation_id) {
                                    $stock = $essential->variation->locationStock()->exists() ? $essential->variation->locationStock()->first() : null;
                                    if ($stock) {
                                        $stock->update(['quantity' => $stock->quantity - $equantity]);
                                    } else {
                                        $essential->variation->stock()->create(['quantity' => 0 - $equantity, 'location_id' => $this->sale->location_id]);
                                    }
                                }
                            } elseif ($essential->item->type == 'recipe') {
                                foreach ($essential->item->portions as $portion) {
                                    foreach ($portion->portionItems as $portion_item) {
                                        $equantity = $equantity * $portion_item->quantity;
                                        $stock     = $portion_item->item->locationStock()->exists() ? $portion_item->item->locationStock()->first() : null;
                                        if ($stock) {
                                            $stock->update(['quantity' => $stock->quantity - $equantity]);
                                        } else {
                                            $portion_item->item->stock()->create(['quantity' => 0 - $equantity, 'location_id' => $this->sale->location_id]);
                                        }
                                        if ($portion_item->item->has_variants && $portion_item->variation_id) {
                                            $stock = $portion_item->variation->locationStock()->exists() ? $portion_item->variation->locationStock()->first() : null;
                                            if ($stock) {
                                                $stock->update(['quantity' => $stock->quantity - $equantity]);
                                            } else {
                                                $portion_item->variation->stock()->create(['quantity' => 0 - $equantity, 'location_id' => $this->sale->location_id]);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if ($portion->choosables) {
                        foreach ($portion->choosables as $choosable) {
                            foreach ($choosable->items as $citem) {
                                $cquantity = $citem->quantity * ($portion->pivot ? $portion->pivot->quantity : 1);
                                if ($citem->item->type == 'standard' || $citem->item->type == 'service') {
                                    $stock = $citem->item->locationStock()->exists() ? $citem->item->locationStock()->first() : null;
                                    if ($stock) {
                                        $stock->update(['quantity' => $stock->quantity - $cquantity]);
                                    } else {
                                        $citem->item->stock()->create(['quantity' => 0 - $cquantity, 'location_id' => $this->sale->location_id]);
                                    }
                                    if ($citem->item->has_variants && $citem->variation_id) {
                                        $stock = $citem->variation->locationStock()->exists() ? $citem->variation->locationStock()->first() : null;
                                        if ($stock) {
                                            $stock->update(['quantity' => $stock->quantity - $cquantity]);
                                        } else {
                                            $citem->variation->stock()->create(['quantity' => 0 - $cquantity, 'location_id' => $this->sale->location_id]);
                                        }
                                    }
                                } elseif ($citem->item->type == 'recipe') {
                                    foreach ($citem->item->portions as $portion) {
                                        foreach ($portion->portionItems as $portion_item) {
                                            $cquantity = $cquantity * $portion_item->quantity;
                                            $stock     = $portion_item->item->locationStock()->exists() ? $portion_item->item->locationStock()->first() : null;
                                            if ($stock) {
                                                $stock->update(['quantity' => $stock->quantity - $cquantity]);
                                            } else {
                                                $portion_item->item->stock()->create(['quantity' => 0 - $cquantity, 'location_id' => $this->sale->location_id]);
                                            }
                                            if ($portion_item->item->has_variants && $portion_item->variation_id) {
                                                $stock = $portion_item->variation->locationStock()->exists() ? $portion_item->variation->locationStock()->first() : null;
                                                if ($stock) {
                                                    $stock->update(['quantity' => $stock->quantity - $cquantity]);
                                                } else {
                                                    $portion_item->variation->stock()->create(['quantity' => 0 - $cquantity, 'location_id' => $this->sale->location_id]);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            if ($item->serials->isNotEmpty()) {
                $item->serials->each->update(['sold' => 1]);
            }
        }

        $loyalty = mps_config('loyalty');
        if ($loyalty) {
            if ($loyalty->customer && $loyalty->customer->spent && $loyalty->customer->points) {
                $points = floor(($this->sale->grand_total / $loyalty->customer->spent) * $loyalty->customer->points);
                if ($points) {
                    $this->sale->customer->update(['points' => $this->sale->customer->points + $points]);
                }
            }
            if ($loyalty->staff && $loyalty->staff->spent && $loyalty->staff->points) {
                $staff_points = usermeta($this->sale->user_id, 'points') ?: 0;
                $points       = floor(($this->sale->grand_total / $loyalty->staff->spent) * $loyalty->staff->points);
                if ($points) {
                    $this->sale->user->meta()->updateOrCreate(
                        ['meta_key' => 'points'],
                        ['meta_key' => 'points', 'meta_value' => $points + $staff_points]
                    );
                }
            }
        }
    }

    private function completedReset($original_sale, $type)
    {
        $original_sale->customer->journal
            ->debitDollars($original_sale->grand_total, 'sale_' . $type)
            ->referencesObject($original_sale);

        if ($original_sale->items) {
            foreach ($original_sale->items as $item) {
                if ($item->item->type == 'standard' || $item->item->type == 'service') {
                    $stock         = $item->stock()->where('location_id', $original_sale->location_id)->first();
                    $base_quantity = convert_to_base_quantity($item->quantity, $item->unit);
                    if ($stock) {
                        $stock->update(['quantity' => $stock->quantity + $base_quantity]);
                    } else {
                        $item->stock()->create(['quantity' => $base_quantity, 'location_id' => $original_sale->location_id]);
                    }
                } elseif ($item->item->type == 'combo' || $item->item->type == 'recipe') {
                    foreach ($item->portions as $portion) {
                        foreach ($portion->essentials as $essential) {
                            $equantity = $essential->quantity * ($portion->pivot ? $portion->pivot->quantity : 1);
                            if ($essential->item->type == 'standard' || $essential->item->type == 'service') {
                                $stock = $essential->item->locationStock()->exists() ? $essential->item->locationStock()->first() : null;
                                // $base_quantity = convert_to_base_quantity($item->quantity, $item->unit);
                                if ($stock) {
                                    $stock->update(['quantity' => $stock->quantity + $equantity]);
                                } else {
                                    $essential->item->stock()->create(['quantity' => $equantity, 'location_id' => $original_sale->location_id]);
                                }
                                if ($essential->item->has_variants && $essential->variation_id) {
                                    $stock = $essential->variation->locationStock()->exists() ? $essential->variation->locationStock()->first() : null;
                                    if ($stock) {
                                        $stock->update(['quantity' => $stock->quantity + $equantity]);
                                    } else {
                                        $essential->variation->stock()->create(['quantity' => $equantity, 'location_id' => $original_sale->location_id]);
                                    }
                                }
                            } elseif ($essential->item->type == 'recipe') {
                                foreach ($essential->item->portions as $portion) {
                                    foreach ($portion->portionItems as $portion_item) {
                                        $equantity = $equantity * $portion_item->quantity;
                                        $stock     = $portion_item->item->locationStock()->exists() ? $portion_item->item->locationStock()->first() : null;
                                        if ($stock) {
                                            $stock->update(['quantity' => $stock->quantity + $equantity]);
                                        } else {
                                            $portion_item->item->stock()->create(['quantity' => $equantity, 'location_id' => $original_sale->location_id]);
                                        }
                                        if ($portion_item->item->has_variants && $portion_item->variation_id) {
                                            $stock = $portion_item->variation->locationStock()->exists() ? $portion_item->variation->locationStock()->first() : null;
                                            if ($stock) {
                                                $stock->update(['quantity' => $stock->quantity + $equantity]);
                                            } else {
                                                $portion_item->variation->stock()->create(['quantity' => $equantity, 'location_id' => $original_sale->location_id]);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        foreach ($portion->choosables as $choosable) {
                            foreach ($choosable->items as $citem) {
                                $cquantity = $citem->quantity * ($portion->pivot ? $portion->pivot->quantity : 1);
                                if ($citem->item->type == 'standard' || $citem->item->type == 'service') {
                                    $stock = $citem->item->locationStock()->exists() ? $citem->item->locationStock()->first() : null;
                                    // $base_quantity = convert_to_base_quantity($item->quantity, $item->unit);
                                    if ($stock) {
                                        $stock->update(['quantity' => $stock->quantity + $cquantity]);
                                    } else {
                                        $citem->item->stock()->create(['quantity' => $cquantity, 'location_id' => $original_sale->location_id]);
                                    }
                                    if ($citem->item->has_variants && $citem->variation_id) {
                                        $stock = $citem->variation->locationStock()->exists() ? $citem->variation->locationStock()->first() : null;
                                        if ($stock) {
                                            $stock->update(['quantity' => $stock->quantity + $cquantity]);
                                        } else {
                                            $citem->variation->stock()->create(['quantity' => $cquantity, 'location_id' => $original_sale->location_id]);
                                        }
                                    }
                                } elseif ($citem->item->type == 'recipe') {
                                    foreach ($citem->item->portions as $portion) {
                                        foreach ($portion->portionItems as $portion_item) {
                                            $cquantity = $cquantity * $portion_item->quantity;
                                            $stock     = $portion_item->item->locationStock()->exists() ? $portion_item->item->locationStock()->first() : null;
                                            if ($stock) {
                                                $stock->update(['quantity' => $stock->quantity + $cquantity]);
                                            } else {
                                                $portion_item->item->stock()->create(['quantity' => $cquantity, 'location_id' => $original_sale->location_id]);
                                            }
                                            if ($portion_item->item->has_variants && $portion_item->variation_id) {
                                                $stock = $portion_item->variation->locationStock()->exists() ? $portion_item->variation->locationStock()->first() : null;
                                                if ($stock) {
                                                    $stock->update(['quantity' => $stock->quantity + $cquantity]);
                                                } else {
                                                    $portion_item->variation->stock()->create(['quantity' => $cquantity, 'location_id' => $original_sale->location_id]);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                if ($item->serials->isNotEmpty()) {
                    $item->serials->each->update(['sold' => 0]);
                }
            }
        }

        $loyalty = mps_config('loyalty');
        if ($loyalty) {
            if ($loyalty->customer && $loyalty->customer->spent && $loyalty->customer->points) {
                $points = floor(($original_sale->grand_total / $loyalty->customer->spent) * $loyalty->customer->points);
                if ($points != 0) {
                    $original_sale->customer->update(['points' => $original_sale->customer->points - $points]);
                }
            }
            if ($loyalty->staff && $loyalty->staff->spent && $loyalty->staff->points) {
                $staff_points = usermeta($this->sale->user_id, 'points') ?: 0;
                $points       = floor(($this->sale->grand_total / $loyalty->staff->spent) * $loyalty->staff->points);
                if ($points) {
                    $this->sale->user->meta()->updateOrCreate(
                        ['meta_key' => 'points'],
                        ['meta_key' => 'points', 'meta_value' => $staff_points - $points]
                    );
                }
            }
        }
    }

    private function created(SaleEvent $event)
    {
        if (!$event->sale->draft) {
            $this->sale = Sale::with(['items', 'customer', 'items.stock'])->find($event->sale->id);
            $this->completed(null, 'created');
        } elseif ($event->sale->shop && safe_email($event->sale->customer->email) && !default_customer($event->sale->customer_id)) {
            $event->sale->customer->notify(new \Modules\MPS\Notifications\SaleCreated($event->sale));
        } elseif ($event->sale->shop && $event->sale->address && safe_email($event->sale->address->email)) {
            $event->sale->address->notify(new \Modules\MPS\Notifications\SaleCreated($event->sale));
        }
    }

    private function deleting(SaleEvent $event)
    {
        if (!$event->sale->draft) {
            $this->sale = Sale::with(['items', 'customer', 'items.stock'])->find($event->sale->id);
            $this->completedReset($this->sale, 'deleting');
        }
    }

    private function updated(SaleEvent $event)
    {
        if ($event->original_sale && !$event->original_sale->draft) {
            $this->completedReset($event->original_sale, 'editing');
        }
        if (!$event->sale->draft) {
            $this->sale = Sale::with(['items', 'customer', 'items.stock'])->find($event->sale->id);
            $this->completed($event->original_sale, 'updated');
        }
    }
}
