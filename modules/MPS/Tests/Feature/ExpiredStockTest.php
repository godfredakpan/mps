<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Item;
use Modules\MPS\Models\Unit;
use Modules\MPS\Models\Brand;
use Modules\MPS\Models\Serial;
use Modules\MPS\Models\Account;
use Modules\MPS\Models\Category;
use Modules\MPS\Models\Location;
use Modules\MPS\Models\Purchase;
use Modules\MPS\Models\Supplier;
use Modules\MPS\Tests\MPSTestCase;
use Modules\MPS\Models\PurchaseItem;

class ExpiredStockTest extends MPSTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->user     = $this->createUser('super');
        $this->unit     = factory(Unit::class)->create();
        $this->brand    = factory(Brand::class)->create();
        $this->account  = factory(Account::class)->create();
        $this->category = factory(Category::class)->create();
        $this->supplier = factory(Supplier::class)->create(['user_id' => $this->user->id]);
        factory('Modules\MPS\Models\Setting')->create(['mps_key' => 'stock', 'mps_value' => '1']);
        $this->location = factory(Location::class)->create(['account_id' => $this->account->id]);
        session(['location_id' => $this->location->id]);
        // $this->item = factory(Item::class)->create();
        // $this->item->categories()->sync($this->category->id);
        // $this->item->stock()->create(['quantity' => 0]);
    }

    public function testExpiredItemsAreBeningREmovedWithStockEdpiredCommand()
    {
        $item = factory(Item::class)->make([
            'code'        => 1,
            'has_serials' => 1,
            'cost'        => 10,
            'unit_id'     => $this->unit->id,
            'brand_id'    => $this->brand->id,
            'category_id' => $this->category->id,
        ])->toArray();
        $item['stock'][] = ['quantity' => 10, 'rack' => 'AB01', 'location_id' => $this->location->id];
        $this->actingAs($this->user)->ajax()->post(module('route') . '/app/items/', $item)->assertOk();
        $this->item = Item::where('code', 1)->first();
        $this->assertEquals(10, $this->item->stock()->first()->quantity);

        // Create purchase
        $res = $this->createPurchase(5, 10, now()->addDays(7));
        $this->assertEquals(15, $this->item->stock()->first()->quantity);
        $this->artisan('stock:expired')
            ->expectsOutput('No item is expiring today or item stock is already adjusted.')->assertExitCode(0);

        $purchase1 = Purchase::with('items.stock')->find($res['data']['id']);
        $purchase1->items->first()->update(['expiry_date' => now()->subDay()->format('Y-m-d')]);
        $this->artisan('stock:expired')->assertExitCode(0);
        $this->assertEquals(10, $this->item->stock()->first()->quantity);
    }

    private function createPurchase($qty, $cost, $expiry = null)
    {
        $serials  = factory(Serial::class, $qty)->make(['item_id' => $this->item->id])->pluck('number')->all();
        $purchase = factory(Purchase::class)->make([
            'draft'       => false,
            'supplier_id' => $this->supplier->id,
            'date'        => now()->format('Y-m-d'),
        ])->toArray();
        $purchase['items'][] = factory(PurchaseItem::class)->make([
            'quantity'     => $qty,
            'cost'         => $cost,
            'net_cost'     => $cost,
            'expiry_date'  => $expiry->format('Y-m-d'),
            'item_id'      => $this->item->id,
            'code'         => $this->item->code,
            'name'         => $this->item->name,
            'unit_id'      => $this->unit->id,
            'item_unit_id' => $this->unit->id,
            'selected'     => ['serials' => $serials],
        ])->toArray();

        $response = $this->actingAs($this->user)->ajax()->post(url(module('route')) . '/app/purchases/', $purchase);
        return $response->json();
    }
}
