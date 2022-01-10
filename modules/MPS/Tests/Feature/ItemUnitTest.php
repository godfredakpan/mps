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

class ItemUnitTest extends MPSTestCase
{
    use TestHelpers;

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

    public function testPurchaseUnitStock()
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
        $purchase_form = $this->createPurchaseForm($this, $this->user, 5, 100, $this->purchase_unit);

        // insert
        $response = $this->actingAs($this->user)->ajax()->post(url(module('route')) . '/app/purchases/', $purchase_form);
        // dd($response->json());
        $response->assertOk();

        $purchase = Purchase::with('items')->find($response['data']['id']);
        $this->assertEquals(0, $purchase->draft);
        foreach ($purchase->items as $item) {
            $this->assertEquals(60, $item->stock()->first()->quantity);
        }

        // update
        $update_form                        = $purchase_form;
        $update_form['items'][0]['unit_id'] = $this->unit->id;
        $response                           = $this->actingAs($this->user)->ajax()->put(url(module('route')) . '/app/purchases/' . $purchase->id, $update_form);
        // dd($response->json());
        $response->assertOk();

        $purchase = $purchase->refresh();
        foreach ($purchase->items as $item) {
            $item->refresh();
            $this->assertEquals(5, $item->stock()->first()->quantity);
        }

        // update again
        $update_form['items'][0]['unit_id']  = $this->purchase_unit->id;
        $update_form['items'][0]['quantity'] = 4;
        $response                            = $this->actingAs($this->user)->ajax()->put(url(module('route')) . '/app/purchases/' . $purchase->id, $update_form);
        // dd($response->json());
        $response->assertOk();

        $purchase = $purchase->refresh();
        foreach ($purchase->items as $item) {
            $item->refresh();
            $this->assertEquals(48, $item->stock()->first()->quantity);
        }

        // delete
        $this->actingAs($this->user)->ajax()->delete(url(module('route')) . '/app/purchases/' . $purchase->id)->assertOk();
        $this->assertDeleted($purchase);
        $this->assertEquals(0, PurchaseItem::count());
        $items = Item::with('stock')->get();
        foreach ($items as $item) {
            $this->assertEquals(0, $item->stock->where('location_id', $this->location->id)->first()->quantity);
        }
    }

    public function testSaleUnitStock()
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

        // Create sale
        $sale_form = $this->createSaleForm($this, $this->user, 500, null, $this->sale_unit);

        // insert
        $response = $this->actingAs($this->user)->ajax()->post(url(module('route')) . '/app/sales/', $sale_form);
        // dd($response->json());
        $response->assertOk();

        $sale = Sale::with('items')->find($response['data']['id']);
        $this->assertEquals(0, $sale->draft);
        foreach ($sale->items as $item) {
            $this->assertEquals(-5, $item->stock()->first()->quantity);
        }

        // update
        $update_form                        = $sale_form;
        $update_form['items'][0]['unit_id'] = $this->unit->id;
        $response                           = $this->actingAs($this->user)->ajax()->put(url(module('route')) . '/app/sales/' . $sale->id, $update_form);
        // dd($response->json());
        $response->assertOk();

        $sale = $sale->refresh();
        foreach ($sale->items as $item) {
            $item->refresh();
            $this->assertEquals(-500, $item->stock()->first()->quantity);
        }

        // update again
        $update_form['items'][0]['unit_id']  = $this->sale_unit->id;
        $update_form['items'][0]['quantity'] = 400;
        $response                            = $this->actingAs($this->user)->ajax()->put(url(module('route')) . '/app/sales/' . $sale->id, $update_form);
        // dd($response->json());
        $response->assertOk();

        $sale = $sale->refresh();
        foreach ($sale->items as $item) {
            $item->refresh();
            $this->assertEquals(-4, $item->stock()->first()->quantity);
        }

        // delete
        $this->actingAs($this->user)->ajax()->delete(url(module('route')) . '/app/sales/' . $sale->id)->assertOk();
        $this->assertDeleted($sale);
        $this->assertEquals(0, SaleItem::count());
        $items = Item::with('stock')->get();
        foreach ($items as $item) {
            $this->assertEquals(0, $item->stock->where('location_id', $this->location->id)->first()->quantity);
        }
    }
}
