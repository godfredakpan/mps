<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Sale;
use Modules\MPS\Models\Customer;
use Modules\MPS\Models\Modifier;
use Modules\MPS\Models\Purchase;
use Modules\MPS\Models\SaleItem;
use Modules\MPS\Models\Supplier;
use Modules\MPS\Models\Quotation;
use Modules\MPS\Models\ReturnOrder;
use Modules\MPS\Models\PurchaseItem;
use Modules\MPS\Models\QuotationItem;
use Modules\MPS\Models\StockTransfer;
use Modules\MPS\Models\ModifierOption;
use Modules\MPS\Models\ReturnOrderItem;
use Modules\MPS\Models\StockAdjustment;
use Modules\MPS\Models\StockTransferItem;
use Modules\MPS\Models\StockAdjustmentItem;

trait TestHelpers
{
    public function createModifier($model, $n, $user)
    {
        $modifiers = factory(Modifier::class, $n < 2 ? 2 : $n)->create()->each(function ($modifier) use ($model) {
            $modifier->options()->create(factory(ModifierOption::class)->make([
                'code'    => $model->item->code,
                'name'    => $model->item->name,
                'item_id' => $model->item->id,
                'unit_id' => $model->item->unit_id,
            ])->toArray());
        });
        return $n < 2 ? $modifiers->first() : $modifiers;
    }

    public function createPurchase($model, $n, $user, $qty = 1, $cost = null, $supplier = null)
    {
        if (!$supplier) {
            $supplier = factory(Supplier::class)->create(['user_id' => $user->id]);
        }
        $purchases = factory(Purchase::class, $n < 2 ? 2 : $n)->create([
            'draft'       => false,
            'user_id'     => $user->id,
            'supplier_id' => $supplier->id,
            'date'        => now()->format('Y-m-d'),
            'grand_total' => $cost ? $cost : $model->item->cost,
        ])->each(function ($purchase) use ($model, $qty, $cost) {
            $purchase->items()->create(
                factory(PurchaseItem::class)->make([
                    'quantity' => $qty,
                    'item_id'  => $model->item->id,
                    'code'     => $model->item->code,
                    'name'     => $model->item->name,
                    'unit_id'  => $model->item->unit_id,
                    'cost'     => $cost ? $cost : $model->item->cost,
                    'net_cost' => $cost ? $cost : $model->item->cost,
                ])->toArray()
            );
        });

        return $n < 2 ? $purchases->first() : $purchases;
    }

    public function createPurchaseForm($model, $user, $qty = 1, $cost = null, $unit = null)
    {
        $supplier = factory(Supplier::class)->create(['user_id' => $user->id]);
        $purchase = factory(Purchase::class)->make([
            'draft'       => false,
            'user_id'     => $user->id,
            'supplier_id' => $supplier->id,
            'date'        => now()->format('Y-m-d'),
        ])->toArray();

        $purchase['items'][] = factory(PurchaseItem::class)->make([
            'quantity'     => $qty,
            'item_id'      => $model->item->id,
            'code'         => $model->item->code,
            'name'         => $model->item->name,
            'cost'         => $cost ? $cost : $model->item->cost,
            'net_cost'     => $cost ? $cost : $model->item->cost,
            'item_unit_id' => $model->item->unit_id,
            'unit_id'      => $unit ? $unit->id : $model->item->purchase_unit_id,
        ])->toArray();

        return $purchase;
    }

    public function createQuotation($model, $n, $user, $qty = 1, $price = null, $unit = null)
    {
        $customer   = factory(Customer::class)->create(['user_id' => $user->id]);
        $quotations = factory(Quotation::class, $n < 2 ? 2 : $n)->create([
            'user_id'     => $user->id,
            'customer_id' => $customer->id,
            'date'        => now()->format('Y-m-d'),
        ])->each(function ($quotation) use ($model, $qty, $price) {
            $quotation->items()->create(
                factory(QuotationItem::class)->make([
                    'quantity'  => $qty,
                    'item_id'   => $model->item->id,
                    'code'      => $model->item->code,
                    'name'      => $model->item->name,
                    'unit_id'   => $model->item->unit_id,
                    'price'     => $price ? $price : $model->item->price,
                    'net_price' => $price ? $price : $model->item->price,
                ])->toArray()
            );
        });

        return $n < 2 ? $quotations->first() : $quotations;
    }

    public function createQuotationForm($model, $user, $qty = 1, $price = null, $unit = null)
    {
        $customer  = factory(Customer::class)->create(['user_id' => $user->id]);
        $quotation = factory(Quotation::class)->make([
            'user_id'     => $user->id,
            'customer_id' => $customer->id,
            'date'        => now()->format('Y-m-d'),
        ])->toArray();

        $quotation['items'][] = factory(QuotationItem::class)->make([
            'quantity'  => $qty,
            'item_id'   => $model->item->id,
            'code'      => $model->item->code,
            'name'      => $model->item->name,
            'price'     => $price ? $price : $model->item->price,
            'net_price' => $price ? $price : $model->item->price,
            'unit_id'   => $unit ? $unit->id : $model->item->sale_unit_id,
        ])->toArray();

        return $quotation;
    }

    public function createReturnOrder($model, $n, $type, $user, $qty = 1, $price = null)
    {
        $customer = null;
        $supplier = null;
        if ($type == 'sale') {
            $customer = factory(Customer::class)->create(['user_id' => $user->id]);
        }
        if ($type == 'purchase') {
            $supplier = factory(Supplier::class)->create(['user_id' => $user->id]);
        }
        $return_orders = factory(ReturnOrder::class, $n < 2 ? 2 : $n)->create([
            'type'        => $type,
            'user_id'     => $user->id,
            'location_id' => $model->location->id,
            'date'        => now()->format('Y-m-d'),
            'customer_id' => $customer ? $customer->id : null,
            'supplier_id' => $supplier ? $supplier->id : null,
        ])->each(function ($return_order) use ($model, $qty, $price) {
            $return_order->items()->create(
                factory(ReturnOrderItem::class)->make([
                    'quantity' => $qty,
                    'code'     => $model->item->code,
                    'name'     => $model->item->name,
                    'item_id'  => $model->item->id,
                ])->toArray()
            );
        });

        return $n < 2 ? $return_orders->first() : $return_orders;
    }

    public function createReturnOrderForm($model, $type, $user, $qty = 1, $price = null)
    {
        $customer = null;
        $supplier = null;
        if ($type == 'sale') {
            $customer = factory(Customer::class)->create(['user_id' => $user->id]);
        }
        if ($type == 'purchase') {
            $supplier = factory(Supplier::class)->create(['user_id' => $user->id]);
        }
        $return_order = factory(ReturnOrder::class)->make([
            'type'        => $type,
            'user_id'     => $user->id,
            'location_id' => $model->location->id,
            'date'        => now()->format('Y-m-d'),
            'customer_id' => $customer ? $customer->id : null,
            'supplier_id' => $supplier ? $supplier->id : null,
        ])->toArray();
        $return_order['items'][] = factory(ReturnOrderItem::class)->make([
            'quantity' => $qty,
            'code'     => $model->item->code,
            'name'     => $model->item->name,
            'item_id'  => $model->item->id,
        ])->toArray();

        return $return_order;
    }

    public function createSale($model, $n, $user, $qty = 1, $price = null, $unit = null, $customer = null)
    {
        if (!$customer) {
            $customer = factory(Customer::class)->create(['user_id' => $user->id]);
        }
        $sales = factory(Sale::class, $n < 2 ? 2 : $n)->create([
            'draft'       => false,
            'user_id'     => $user->id,
            'customer_id' => $customer->id,
            'date'        => now()->format('Y-m-d'),
            'grand_total' => $price ? $price : $model->item->price,
        ])->each(function ($sale) use ($model, $qty, $price, $unit) {
            $sale->items()->create(
                factory(SaleItem::class)->make([
                    'quantity'  => $qty,
                    'item_id'   => $model->item->id,
                    'code'      => $model->item->code,
                    'name'      => $model->item->name,
                    'price'     => $price ? $price : $model->item->price,
                    'net_price' => $price ? $price : $model->item->price,
                    'unit_id'   => $unit ? $unit->id : $model->item->unit_id,
                ])->toArray()
            );
        });
        return $n < 2 ? $sales->first() : $sales;
    }

    public function createSaleForm($model, $user, $qty = 1, $price = null, $unit = null)
    {
        $customer = factory(Customer::class)->create(['user_id' => $user->id]);
        $sale     = factory(Sale::class)->make([
            'draft'       => false,
            'user_id'     => $user->id,
            'customer_id' => $customer->id,
            'date'        => now()->format('Y-m-d'),
        ])->toArray();

        $sale['items'][] = factory(SaleItem::class)->make([
            'quantity'  => $qty,
            'item_id'   => $model->item->id,
            'code'      => $model->item->code,
            'name'      => $model->item->name,
            'price'     => $price ? $price : $model->item->price,
            'net_price' => $price ? $price : $model->item->price,
            'unit_id'   => $unit ? $unit->id : $model->item->sale_unit_id,
        ])->toArray();

        return $sale;
    }

    public function createStockAdjustment($model, $n, $user, $qty = 1, $cost = null)
    {
        $stock_adjustments = factory(StockAdjustment::class, $n < 2 ? 2 : $n)->create([
            'draft'   => false,
            'user_id' => $user->id,
            'date'    => now()->format('Y-m-d'),
        ])->each(function ($stock_adjustment) use ($model, $qty, $cost) {
            $stock_adjustment->items()->create(
                factory(StockAdjustmentItem::class)->make([
                    'quantity' => $qty,
                    'item_id'  => $model->item->id,
                    'code'     => $model->item->code,
                    'name'     => $model->item->name,
                    'unit_id'  => $model->item->unit_id,
                    'cost'     => $cost ? $cost : $model->item->cost,
                    'net_cost' => $cost ? $cost : $model->item->cost,
                ])->toArray()
            );
        });
        return $n < 2 ? $stock_adjustments->first() : $stock_adjustments;
    }

    public function createStockAdjustmentForm($model, $user, $type, $draft = false, $qty = 1, $cost = null, $unit = null)
    {
        $stock_adjustment = factory(StockAdjustment::class)->make([
            'type'    => $type,
            'draft'   => $draft,
            'user_id' => $user->id,
            'date'    => now()->format('Y-m-d'),
        ])->toArray();

        $stock_adjustment['items'][] = factory(StockAdjustmentItem::class)->make([
            'quantity'     => $qty,
            'item_id'      => $model->item->id,
            'code'         => $model->item->code,
            'name'         => $model->item->name,
            'cost'         => $cost ? $cost : $model->item->cost,
            'net_cost'     => $cost ? $cost : $model->item->cost,
            'item_unit_id' => $unit ? $unit->id : $model->item->unit_id,
            'unit_id'      => $unit ? $unit->id : $model->item->purchase_unit_id,
        ])->toArray();

        return $stock_adjustment;
    }

    public function createStockTransfer($model, $n, $user)
    {
        $stock_transfers = factory(StockTransfer::class, $n < 2 ? 2 : $n)->create([
            'user_id' => $user->id,
            'status'  => 'transferred',
            'to'      => $model->toLocation->id,
            'from'    => $model->fromLocation->id,
        ])->each(function ($stock_transfer) use ($model) {
            $stock_transfer->items()->create(factory(StockTransferItem::class)->make([
                'code'    => $model->item->code,
                'name'    => $model->item->name,
                'item_id' => $model->item->id,
                'unit_id' => $model->item->unit_id,
            ])->toArray());
        });
        return $n < 2 ? $stock_transfers->first() : $stock_transfers;
    }
}
