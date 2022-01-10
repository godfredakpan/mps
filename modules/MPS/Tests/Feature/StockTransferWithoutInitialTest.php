<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Item;
use Modules\MPS\Models\Unit;
use Modules\MPS\Models\Brand;
use Modules\MPS\Models\Account;
use Modules\MPS\Models\Category;
use Modules\MPS\Models\Location;
use Modules\MPS\Tests\MPSTestCase;
use Modules\MPS\Models\StockTransfer;

class StockTransferWithoutInitialTest extends MPSTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->user      = $this->createUser('super');
        $this->unit      = factory(Unit::class)->create();
        $this->brand     = factory(Brand::class)->create();
        $this->account   = factory(Account::class)->create();
        $this->category  = factory(Category::class)->create();
        $this->route     = url(module('route')) . '/app/transfers/stock/';
        $this->locations = factory(Location::class, 2)->create(['account_id' => $this->account->id]);

        $this->items = factory(Item::class, 5)->create()->each(function ($item) {
            foreach ($this->locations as $location) {
                session(['location_id' => $location->id]);
                // $item->stock()->create(['quantity' => 20]);
            }
        });
    }

    public function testMPSCanCreatAndUpdateStockTransferIfNoItemStock()
    {
        $item         = $this->items->first();
        $toLocation   = $this->locations->last();
        $fromLocation = $this->locations->first();
        // insert
        $stock_transfer = factory(StockTransfer::class)->make([
            'status' => 'transferred',
            'to'     => $toLocation->id,
            'from'   => $fromLocation->id,
            'date'   => now()->format('Y-m-d'),
        ])->toArray();
        $stock_transfer['items'][] = [
            'id'       => $item->id,
            'item_id'  => $item->id,
            'code'     => $item->code,
            'name'     => $item->name,
            'quantity' => 10,
        ];
        $this->actingAs($this->user)->ajax()->post($this->route, $stock_transfer)->assertOk();
        $this->assertEquals(10, $item->stock->where('location_id', $toLocation->id)->first()->quantity);
        $this->assertEquals(-10, $item->stock->where('location_id', $fromLocation->id)->first()->quantity);

        // update
        $stock_transfer = StockTransfer::first();
        $update         = factory(StockTransfer::class)->make([
            'status' => 'transferred',
            'to'     => $toLocation->id,
            'from'   => $fromLocation->id,
            'date'   => now()->format('Y-m-d'),
        ])->toArray();
        $update['items'][] = [
            'id'       => $item->id,
            'item_id'  => $item->id,
            'code'     => $item->code,
            'name'     => $item->name,
            'quantity' => 5,
        ];
        $this->actingAs($this->user)->ajax()->put($this->route . $stock_transfer->id, $update)->assertOk();

        // TODO
        $stock_transfer = $stock_transfer->refresh();
        $item           = Item::with('stock')->find($item->id);
        $this->assertEquals($update['to'], $stock_transfer->to);
        $this->assertEquals($update['from'], $stock_transfer->from);
        $this->assertEquals($update['status'], $stock_transfer->status);
        $this->assertEquals(5, $item->stock->where('location_id', $toLocation->id)->first()->quantity);
        $this->assertEquals(-5, $item->stock->where('location_id', $fromLocation->id)->first()->quantity);

        // delete
        $this->actingAs($this->user)->ajax()->delete($this->route . $stock_transfer->id)->assertOk();
        $this->assertDeleted($stock_transfer);
        $item = Item::with('stock')->find($item->id);
        $this->assertEquals(0, $item->stock->where('location_id', $toLocation->id)->first()->quantity);
        $this->assertEquals(0, $item->stock->where('location_id', $fromLocation->id)->first()->quantity);
    }
}
