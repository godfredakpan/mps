<?php

namespace Modules\MPS\Tests\Feature;

use Carbon\Carbon;
use Modules\MPS\Models\Item;
use Modules\MPS\Models\Sale;
use Modules\MPS\Models\Unit;
use Modules\MPS\Models\Brand;
use Modules\MPS\Models\Account;
use Modules\MPS\Models\Costing;
use Modules\MPS\Models\Category;
use Modules\MPS\Models\Customer;
use Modules\MPS\Models\Location;
use Modules\MPS\Models\Purchase;
use Modules\MPS\Models\SaleItem;
use Modules\MPS\Models\Supplier;
use Modules\MPS\Tests\MPSTestCase;
use Modules\MPS\Models\PurchaseItem;

class InventoryAccountingTest extends MPSTestCase
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
        $this->item = factory(Item::class)->create();
        $this->item->categories()->sync($this->category->id);
        $this->item->stock()->create(['quantity' => 0]);
    }

    public function testAVCO()
    {
        // First purcahse of 5
        factory('Modules\MPS\Models\Setting')->create(['mps_key' => 'inventory_accounting', 'mps_value' => 'AVCO']);

        $item = factory(Item::class)->make([
            'code'        => 1,
            'cost'        => 10,
            'unit_id'     => $this->unit->id,
            'brand_id'    => $this->brand->id,
            'category_id' => $this->category->id,
        ])->toArray();
        $item['stock'][] = ['quantity' => 10, 'rack' => 'AB01', 'location_id' => $this->location->id];
        $this->actingAs($this->user)->ajax()->post(module('route') . '/app/items/', $item)->assertOk();
        $this->item = Item::where('code', 1)->first();
        $this->assertEquals($this->item->cost, $this->item->stock->first()->avg_cost);

        $res = $this->createPurchase(5, 10);
        // $purchase1 = Purchase::with('items')->find($res['data']['id']);
        $this->assertEquals((float) formatDecimal(((10 * 10) + (10 * 5)) / (10 + 5)), $this->item->stock()->first()->avg_cost);

        // Second purcahse of 5 @ 14
        $res = $this->createPurchase(5, 14);
        // $purchase1 = Purchase::with('items')->find($res['data']['id']);
        $this->assertEquals((float) formatDecimal(((10 * 15) + (14 * 5)) / (15 + 5)), $this->item->stock()->first()->avg_cost);

        // Create sale
        $res   = $this->createSale(4, 20);
        $sale1 = Sale::with('items')->find($res['data']['id']);
        $this->assertEquals(1, Costing::where('sale_id', $sale1->id)->where('cost', 11)->count());
        // $stock = $this->item->stock()->first();
        // dd(formatDecimal(((10 * 15) + (14 * 5)) / (15 + 5)), $stock->avg_cost, $stock->quantity);

        // Third purcahse of 5 @ 18
        $res = $this->createPurchase(5, 18);
        // $purchase1 = Purchase::with('items')->find($res['data']['id']);
        $this->assertEquals((float) formatDecimal(((11 * 16) + (18 * 5)) / (16 + 5)), $this->item->stock()->first()->avg_cost);

        // Create 2nd sale
        $res   = $this->createSale(4, 20);
        $sale2 = Sale::with('items')->find($res['data']['id']);
        $this->assertEquals(2, Costing::where('sale_id', $sale2->id)->where('cost', formatDecimal(((11 * 16) + (18 * 5)) / (16 + 5)))->count());

        // Create 3rd sale
        $res   = $this->createSale(7, 20);
        $sale3 = Sale::with('items')->find($res['data']['id']);
        $this->assertEquals(2, Costing::where('sale_id', $sale3->id)->where('cost', formatDecimal(((11 * 16) + (18 * 5)) / (16 + 5)))->count());

        $this->assertEquals(5, Costing::count());
        $this->assertEquals(3, Sale::count());
        $this->assertEquals(3, Purchase::count());
    }

    public function testFIFO()
    {
        factory('Modules\MPS\Models\Setting')->create(['mps_key' => 'inventory_accounting', 'mps_value' => 'FIFO']);
        Carbon::setTestNow(now()->subDays(5));
        // First purcahse of 5 @ 10
        $res = $this->createPurchase(5, 10);
        // $purchase1 = Purchase::with('items')->find($res['data']['id']);

        Carbon::setTestNow();
        Carbon::setTestNow(now()->subDays(4));
        // Second purcahse of 5 @ 14
        $res = $this->createPurchase(5, 14);
        // $purchase1 = Purchase::with('items')->find($res['data']['id']);

        Carbon::setTestNow();
        Carbon::setTestNow(now()->subDays(3));
        // Third purcahse of 5 @ 18
        $res = $this->createPurchase(5, 18);
        // $purchase1 = Purchase::with('items')->find($res['data']['id']);

        Carbon::setTestNow();
        // Create sale
        $res   = $this->createSale(4, 20);
        $sale1 = Sale::with('items')->find($res['data']['id']);
        $this->assertEquals(1, Costing::where('sale_id', $sale1->id)->where('cost', 10)->count());

        // Create 2nd sale
        $res   = $this->createSale(4, 20);
        $sale2 = Sale::with('items')->find($res['data']['id']);
        $this->assertEquals(1, Costing::where('sale_id', $sale2->id)->where('cost', 10)->count());
        $this->assertEquals(1, Costing::where('sale_id', $sale2->id)->where('cost', 14)->count());

        // Create 3rd sale
        $res   = $this->createSale(7, 20);
        $sale3 = Sale::with('items')->find($res['data']['id']);
        $this->assertEquals(1, Costing::where('sale_id', $sale3->id)->where('cost', 14)->count());
        $this->assertEquals(1, Costing::where('sale_id', $sale3->id)->where('cost', 18)->count());

        $this->assertEquals(5, Costing::count());
        $this->assertEquals(3, Sale::count());
        $this->assertEquals(3, Purchase::count());
    }

    public function testITEX()
    {
        // First purcahse of 5
        factory('Modules\MPS\Models\Setting')->create(['mps_key' => 'inventory_accounting', 'mps_value' => 'ITEX']);
        $res = $this->createPurchase(5, 10, now()->subDays(2)->format('Y-m-d'));
        // $purchase1 = Purchase::with('items')->find($res['data']['id']);

        // Second purcahse of 5 @ 14
        $res = $this->createPurchase(5, 14, now()->format('Y-m-d'));
        // $purchase1 = Purchase::with('items')->find($res['data']['id']);

        // Third purcahse of 5 @ 18
        $res = $this->createPurchase(5, 18, now()->subDays(4)->format('Y-m-d'));
        // $purchase1 = Purchase::with('items')->find($res['data']['id']);

        // Create sale
        $res   = $this->createSale(4, 20);
        $sale1 = Sale::with('items')->find($res['data']['id']);
        // dd(PurchaseItem::orderBy('expiry_date', 'asc')->get()->toArray());
        $this->assertEquals(1, Costing::where('sale_id', $sale1->id)->where('cost', 18)->count());

        // Create 2nd sale
        $res   = $this->createSale(4, 20);
        $sale2 = Sale::with('items')->find($res['data']['id']);
        $this->assertEquals(1, Costing::where('sale_id', $sale2->id)->where('cost', 18)->count());
        $this->assertEquals(1, Costing::where('sale_id', $sale2->id)->where('cost', 10)->count());

        // Create 3rd sale
        $res   = $this->createSale(7, 20);
        $sale3 = Sale::with('items')->find($res['data']['id']);
        $this->assertEquals(1, Costing::where('sale_id', $sale3->id)->where('cost', 10)->count());
        $this->assertEquals(1, Costing::where('sale_id', $sale3->id)->where('cost', 14)->count());

        $this->assertEquals(5, Costing::count());
        $this->assertEquals(3, Sale::count());
        $this->assertEquals(3, Purchase::count());
    }

    public function testLIFO()
    {
        // First purcahse of 5
        factory('Modules\MPS\Models\Setting')->create(['mps_key' => 'inventory_accounting', 'mps_value' => 'LIFO']);
        Carbon::setTestNow();
        Carbon::setTestNow(now()->subDays(4));
        $res = $this->createPurchase(5, 10);
        // $purchase1 = Purchase::with('items')->find($res['data']['id']);

        Carbon::setTestNow();
        Carbon::setTestNow(now()->subDays(3));
        // Second purcahse of 5 @ 14
        $res = $this->createPurchase(5, 14);
        // $purchase1 = Purchase::with('items')->find($res['data']['id']);
        Carbon::setTestNow();
        Carbon::setTestNow(now()->subDays(2));
        // Third purcahse of 5 @ 18
        $res = $this->createPurchase(5, 18);
        // $purchase1 = Purchase::with('items')->find($res['data']['id']);

        Carbon::setTestNow();
        // Create sale
        $res   = $this->createSale(4, 20);
        $sale1 = Sale::with('items')->find($res['data']['id']);
        $this->assertEquals(1, Costing::where('sale_id', $sale1->id)->where('cost', 18)->count());

        // Create 2nd sale
        $res   = $this->createSale(4, 20);
        $sale2 = Sale::with('items')->find($res['data']['id']);
        $this->assertEquals(1, Costing::where('sale_id', $sale2->id)->where('cost', 18)->count());
        $this->assertEquals(1, Costing::where('sale_id', $sale2->id)->where('cost', 14)->count());

        // Create 3rd sale
        $res   = $this->createSale(7, 20);
        $sale3 = Sale::with('items')->find($res['data']['id']);
        $this->assertEquals(1, Costing::where('sale_id', $sale3->id)->where('cost', 14)->count());
        $this->assertEquals(1, Costing::where('sale_id', $sale3->id)->where('cost', 10)->count());

        $this->assertEquals(5, Costing::count());
        $this->assertEquals(3, Sale::count());
        $this->assertEquals(3, Purchase::count());
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
