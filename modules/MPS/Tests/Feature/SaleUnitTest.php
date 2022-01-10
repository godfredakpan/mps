<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Item;
use Modules\MPS\Models\Sale;
use Modules\MPS\Models\Unit;
use Modules\MPS\Models\Brand;
use Modules\MPS\Models\Account;
use Modules\MPS\Models\Category;
use Modules\MPS\Models\Customer;
use Modules\MPS\Models\Location;
use Modules\MPS\Models\Purchase;
use Modules\MPS\Models\SaleItem;
use Modules\MPS\Models\Supplier;
use Modules\MPS\Tests\MPSTestCase;
use Modules\MPS\Models\PurchaseItem;

class SaleUnitTest extends MPSTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->user          = $this->createUser('super');
        $this->brand         = factory(Brand::class)->create();
        $this->account       = factory(Account::class)->create();
        $this->category      = factory(Category::class)->create();
        $this->customer      = factory(Customer::class)->create(['user_id' => $this->user->id]);
        $this->supplier      = factory(Supplier::class)->create(['user_id' => $this->user->id]);
        $this->unit          = factory(Unit::class)->create(['name' => 'Meter', 'code' => 'm']);
        $this->purchase_unit = factory(Unit::class)->create(['name' => 'Dozen', 'code' => '12m', 'base_id' => $this->unit->id, 'operator' => '*', 'operation_value' => 12]);
        $this->sale_unit     = factory(Unit::class)->create(['name' => 'Centimeter', 'code' => 'cm', 'base_id' => $this->unit->id, 'operator' => '/', 'operation_value' => 100]);
        factory('Modules\MPS\Models\Setting')->create(['mps_key' => 'stock', 'mps_value' => '1']);
        $this->location = factory(Location::class)->create(['account_id' => $this->account->id]);
        session(['location_id' => $this->location->id]);
        // $this->item = factory(Item::class)->create();
        // $this->item->categories()->sync($this->category->id);
        // $this->item->stock()->create(['quantity' => 0]);
    }

    public function testPurchaseAndSaleUnitAndStock()
    {
        $item = factory(Item::class)->make([
            'code'        => 1,
            'cost'        => 10,
            'unit_id'     => $this->unit->id,
            'brand_id'    => $this->brand->id,
            'category_id' => $this->category->id,
        ])->toArray();
        $item['stock'][] = ['quantity' => 0, 'rack' => 'AB01', 'location_id' => $this->location->id];
        $this->actingAs($this->user)->ajax()->post(module('route') . '/app/items/', $item)->assertOk();
        $this->item = Item::where('code', 1)->first();

        // Create purchase
        $res       = $this->createPurchase(5, 100);
        $purchase1 = Purchase::with('items')->find($res['data']['id']);
        $this->assertEquals(60, $this->item->stock()->first()->quantity);

        // Create sale
        $res   = $this->createSale(100, 20, $this->sale_unit);
        $sale1 = Sale::with('items')->find($res['data']['id']);
        $this->assertEquals(59, $this->item->stock()->first()->quantity);

        // Create 2nd sale
        $res   = $this->createSale(1, 200, $this->purchase_unit);
        $sale2 = Sale::with('items')->find($res['data']['id']);
        $this->assertEquals(47, $this->item->stock()->first()->quantity);

        // Create 2nd purchase
        $res       = $this->createPurchase(3, 30, true);
        $purchase2 = Purchase::with('items')->find($res['data']['id']);
        $this->assertEquals(50, $this->item->stock()->first()->quantity);
    }

    private function createPurchase($qty, $cost, $unit = null)
    {
        $purchase = factory(Purchase::class)->make([
            'draft'       => false,
            'supplier_id' => $this->supplier->id,
            'date'        => now()->format('Y-m-d'),
        ])->toArray();
        $purchase['items'][] = factory(PurchaseItem::class)->make([
            'quantity'     => $qty,
            'cost'         => $cost,
            'net_cost'     => $cost,
            'item_id'      => $this->item->id,
            'code'         => $this->item->code,
            'name'         => $this->item->name,
            'unit_id'      => $unit ? $this->unit->id : $this->purchase_unit->id,
            'item_unit_id' => $this->unit->id,
        ])->toArray();

        $response = $this->actingAs($this->user)->ajax()->post(url(module('route')) . '/app/purchases/', $purchase);
        return $response->json();
    }

    private function createSale($qty, $price, $unit = null)
    {
        $sale = factory(Sale::class)->make([
            'draft'       => false,
            'customer_id' => $this->customer->id,
            'date'        => now()->format('Y-m-d'),
        ])->toArray();
        $sale['items'][] = factory(SaleItem::class)->make([
            'quantity'  => $qty,
            'price'     => $price,
            'net_price' => $price,
            'item_id'   => $this->item->id,
            'code'      => $this->item->code,
            'name'      => $this->item->name,
            'unit_id'   => $unit ? $unit->id : $this->sale_unit->id,
        ])->toArray();

        $response = $this->actingAs($this->user)->ajax()->post(url(module('route')) . '/app/sales/', $sale);
        return $response->json();
    }
}
