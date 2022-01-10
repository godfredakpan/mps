<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Tax;
use Modules\MPS\Models\Item;
use Modules\MPS\Models\Unit;
use Modules\MPS\Models\Brand;
use Modules\MPS\Models\Account;
use Modules\MPS\Models\Category;
use Modules\MPS\Models\Location;
use Modules\MPS\Tests\MPSTestCase;
use Modules\MPS\Models\StockAdjustment;
use Modules\MPS\Models\StockAdjustmentItem;

class StockAdjustmentTest extends MPSTestCase
{
    use TestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user    = $this->createUser('super');
        $this->unit    = factory(Unit::class)->create();
        $this->brand   = factory(Brand::class)->create();
        $this->account = factory(Account::class)->create();
        $category      = factory(Category::class)->create();
        $this->route   = url(module('route')) . '/app/adjustments/';
        factory('Modules\MPS\Models\Setting')->create(['mps_key' => 'stock', 'mps_value' => '1']);
        $locations = factory(Location::class, 2)->create(['account_id' => $this->account->id]);

        $taxes[]         = factory(Tax::class)->create(['name' => 'CGST @ 9%', 'code' => 'cgst@9', 'rate' => 9, 'compound' => false, 'state' => true, 'same' => true])->toArray()['id'];
        $taxes[]         = factory(Tax::class)->create(['name' => 'SGST @ 9%', 'code' => 'sgst@9', 'rate' => 9, 'compound' => false, 'state' => true, 'same' => true])->toArray()['id'];
        $taxes[]         = factory(Tax::class)->create(['name' => 'IGST @ 18%', 'code' => 'igst@18', 'rate' => 18, 'compound' => false, 'recoverable' => true, 'state' => true, 'same' => false])->toArray()['id'];
        $this->locations = $locations;
        $this->items     = factory(Item::class, 5)->create()->each(function ($item) use ($category, $locations, $taxes) {
            $item->taxes()->sync($taxes);
            $item->categories()->sync($category->id);
            foreach ($locations as $location) {
                session(['location_id' => $location->id]);
                $item->stock()->create(['quantity' => 20]);
            }
        });
    }

    public function testMPSCanCreatAndUpdateStockAdjustment()
    {
        // Check with draft true
        $this->item = $this->items->first();
        $location   = $this->locations->first();
        session(['location_id' => $location->id]);
        $new_stock_adjustment1 = $this->createStockAdjustmentForm($this, $this->user, 'addition', true, 4);

        // insert
        $response = $this->actingAs($this->user)->ajax()->post($this->route, $new_stock_adjustment1);
        // dd($response->json());
        $response->assertOk();

        // check
        $update           = $new_stock_adjustment1;
        $stock_adjustment = StockAdjustment::with('items')->find($response['data']['id']);
        $this->assertEquals(1, $stock_adjustment->draft);
        foreach ($stock_adjustment->items as $item) {
            $this->assertEquals(20, $item->stock()->first()->quantity);
        }
        $update['draft'] = false;
        $update['date']  = now()->subDays(2)->format('Y-m-d');
        $response        = $this->actingAs($this->user)->ajax()->put($this->route . $stock_adjustment->id, $update);
        // dd($response->json());
        $response->assertOk();

        // update
        $stock_adjustment = $stock_adjustment->refresh();
        $this->assertEquals(0, $stock_adjustment->draft);
        $this->assertEquals($update['date'], $stock_adjustment->date->toDateString());
        foreach ($stock_adjustment->items as $item) {
            $item->refresh();
            $this->assertEquals(20 + $item->quantity, $item->stock()->first()->quantity);
        }

        // delete
        $this->actingAs($this->user)->ajax()->delete($this->route . $stock_adjustment->id)->assertOk();
        $this->assertDeleted($stock_adjustment);
        $this->assertEquals(0, StockAdjustmentItem::count());
        $items = Item::with('stock')->get();
        foreach ($items as $item) {
            $this->assertEquals(20, $item->stock->where('location_id', $location->id)->first()->quantity);
        }

        // Check with draft false
        $new_stock_adjustment2          = $new_stock_adjustment1;
        $new_stock_adjustment2['draft'] = false;
        $response2                      = $this->actingAs($this->user)->ajax()->post($this->route, $new_stock_adjustment2);
        $response2->assertOk();

        $update           = $new_stock_adjustment2;
        $stock_adjustment = StockAdjustment::with('items')->find($response2['data']['id']);
        $this->assertEquals(0, $stock_adjustment->draft);
        foreach ($stock_adjustment->items as $item) {
            $this->assertEquals(20 + $item->quantity, $item->stock()->first()->quantity);
        }
        $update['draft'] = true;
        $update['date']  = now()->subDays(2)->format('Y-m-d');
        $this->actingAs($this->user)->ajax()->put($this->route . $stock_adjustment->id, $update)->assertOk();

        // update
        $stock_adjustment = $stock_adjustment->refresh();
        $this->assertEquals(1, $stock_adjustment->draft);
        $this->assertEquals($update['date'], $stock_adjustment->date->toDateString());
        foreach ($stock_adjustment->items as $item) {
            $item->refresh();
            $this->assertEquals(20, $item->stock()->first()->quantity);
        }

        $this->actingAs($this->user)->ajax()->delete($this->route . $stock_adjustment->id)->assertOk();
        $this->assertDeleted($stock_adjustment);
        $this->assertEquals(0, StockAdjustmentItem::count());
        foreach ($stock_adjustment->items as $item) {
            $this->assertEquals(20, $item->stock->where('location_id', $location->id)->first()->quantity);
        }

        // damage
        $location = $this->locations->first();
        session(['location_id' => $location->id]);
        $new_stock_adjustment1 = $this->createStockAdjustmentForm($this, $this->user, 'damage', false, 2);
        $response              = $this->actingAs($this->user)->ajax()->post($this->route, $new_stock_adjustment1);
        // dd($response->json());
        $response->assertOk();

        $update           = $this->createStockAdjustmentForm($this, $this->user, 'damage', false, 4);
        $stock_adjustment = StockAdjustment::with('items')->find($response['data']['id']);
        $this->assertEquals(0, $stock_adjustment->draft);
        foreach ($stock_adjustment->items as $item) {
            $this->assertEquals(20 - $item->quantity, $item->stock()->first()->quantity);
        }

        $update['date'] = now()->subDays(4)->format('Y-m-d');
        $this->actingAs($this->user)->ajax()->put($this->route . $stock_adjustment->id, $update)->assertOk();

        // update
        $stock_adjustment = $stock_adjustment->refresh();
        $this->assertEquals(0, $stock_adjustment->draft);
        $this->assertEquals($update['date'], $stock_adjustment->date->toDateString());
        foreach ($stock_adjustment->items as $item) {
            $item->refresh();
            $this->assertEquals(20 - $item->quantity, $item->stock()->first()->quantity);
        }

        // subtract
        $location = $this->locations->first();
        session(['location_id' => $location->id]);
        $new_stock_adjustment1 = $this->createStockAdjustmentForm($this, $this->user, 'subtraction', false, 2);
        $response              = $this->actingAs($this->user)->ajax()->post($this->route, $new_stock_adjustment1);
        // dd($response->json());
        $response->assertOk();

        $stock_adjustment = StockAdjustment::with('items')->find($response['data']['id']);
        $this->assertEquals(0, $stock_adjustment->draft);
        foreach ($stock_adjustment->items as $item) {
            $this->assertEquals(16 - $item->quantity, $item->stock()->first()->quantity);
        }

        $this->actingAs($this->user)->ajax()->delete($this->route . $stock_adjustment->id)->assertOk();
        $this->assertDeleted($stock_adjustment);
        $this->assertEquals(1, StockAdjustmentItem::count());
        foreach ($stock_adjustment->items as $item) {
            $this->assertEquals(16, $item->stock->where('location_id', $location->id)->first()->quantity);
        }
    }

    public function testMPSStockAdjustmentValidation()
    {
        $response = $this->actingAs($this->user)->ajax()->post($this->route, []);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
        $response->assertJsonValidationErrors(['date', 'type', 'items']);

        $response2 = $this->actingAs($this->user)->ajax()->post($this->route, ['items' => [['id' => 'test']]]);
        $response2->assertStatus(422);
        $response2->assertJsonStructure(['message', 'errors']);
        $response2->assertJsonValidationErrors(['date', 'type', 'items.0.code', 'items.0.name', 'items.0.cost', 'items.0.quantity']);
    }
}
