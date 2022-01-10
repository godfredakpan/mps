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
use Modules\MPS\Models\OverSelling;
use Modules\MPS\Models\PurchaseItem;

class OverSellingTest extends MPSTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->user          = $this->createUser('super');
        $this->unit          = factory(Unit::class)->create();
        $this->brand         = factory(Brand::class)->create();
        $this->account       = factory(Account::class)->create();
        $this->category      = factory(Category::class)->create();
        $this->saleRoute     = url(module('route')) . '/app/sales/';
        $this->purchaseRoute = url(module('route')) . '/app/purchases/';
        $this->customer      = factory(Customer::class)->create(['user_id' => $this->user->id]);
        $this->supplier      = factory(Supplier::class)->create(['user_id' => $this->user->id]);
        factory('Modules\MPS\Models\Setting')->create(['mps_key' => 'stock', 'mps_value' => '1']);
        $this->location = factory(Location::class)->create(['account_id' => $this->account->id]);

        session(['location_id' => $this->location->id]);
        // $this->item = factory(Item::class)->create();
        // $this->item->categories()->sync($this->category->id);
        // $this->item->stock()->create(['quantity' => 0]);
    }

    public function testCanSellOutOfStockItems()
    {
        $item = factory(Item::class)->make([
            'code'        => 1,
            'cost'        => 10,
            'unit_id'     => $this->unit->id,
            'brand_id'    => $this->brand->id,
            'category_id' => $this->category->id,
        ])->toArray();
        $item['stock'][] = ['quantity' => 2, 'rack' => 'AB01', 'location_id' => $this->location->id];
        $this->actingAs($this->user)->ajax()->post(module('route') . '/app/items/', $item)->assertOk();
        $this->item = Item::where('code', 1)->first();

        // Create sale
        $res   = $this->createSale(4, 20);
        $sale1 = Sale::with('items')->find($res['data']['id']);
        $this->assertEquals(-2, $this->item->stock()->first()->quantity);
        // TODO OverSelling to check item stock quantity
        // below should be 2 not 4
        $this->assertEquals(4, OverSelling::where('sale_id', $sale1->id)->first()->quantity);
    }

    public function testNewPurcahseSetTheCorrectStock()
    {
        $item = factory(Item::class)->make([
            'code'        => 1,
            'cost'        => 10,
            'unit_id'     => $this->unit->id,
            'brand_id'    => $this->brand->id,
            'category_id' => $this->category->id,
        ])->toArray();
        $item['stock'][] = ['quantity' => 2, 'rack' => 'AB01', 'location_id' => $this->location->id];
        $this->actingAs($this->user)->ajax()->post(module('route') . '/app/items/', $item)->assertOk();
        $this->item = Item::where('code', 1)->first();

        // Create sale
        $res   = $this->createSale(4, 20);
        $sale1 = Sale::with('items')->find($res['data']['id']);
        $this->assertEquals(-2, $this->item->stock()->first()->quantity);
        $this->assertEquals(1, OverSelling::where('sale_id', $sale1->id)->count());
        // TODO OverSelling to check item stock quantity
        // below should be 2 not 4
        $this->assertEquals(4, OverSelling::where('sale_id', $sale1->id)->first()->quantity);

        $res = $this->createPurchase(5, 10);
        $this->assertEquals(0, OverSelling::count());
        $this->assertEquals(3, $this->item->stock()->first()->quantity);

        // Create 2nd sale
        $res   = $this->createSale(5, 20);
        $sale2 = Sale::with('items')->find($res['data']['id']);
        $this->assertEquals(-2, $this->item->stock()->first()->quantity);
        $this->assertEquals(1, OverSelling::where('sale_id', $sale2->id)->count());
        // TODO OverSelling to check item stock quantity
        // below should be 1 not 5
        $this->assertEquals(5, OverSelling::where('sale_id', $sale2->id)->first()->quantity);
    }

    private function createPurchase($qty, $cost, $expiry = null)
    {
        $purchase = factory(Purchase::class)->make([
            'draft'       => false,
            'supplier_id' => $this->supplier->id,
            'date'        => now()->format('Y-m-d'),
        ])->toArray();
        $purchase['items'][] = factory(PurchaseItem::class)->make([
            'quantity'    => $qty,
            'cost'        => $cost,
            'net_cost'    => $cost,
            'expiry_date' => $expiry,
            'item_id'     => $this->item->id,
            'code'        => $this->item->code,
            'name'        => $this->item->name,
        ])->toArray();

        $response = $this->actingAs($this->user)->ajax()->post($this->purchaseRoute, $purchase);
        return $response->json();
    }

    private function createSale($qty, $price)
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
        ])->toArray();

        $response = $this->actingAs($this->user)->ajax()->post($this->saleRoute, $sale);
        return $response->json();
    }
}
