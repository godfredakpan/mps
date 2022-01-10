<?php

namespace Modules\MPS\Tests\Feature;

use Carbon\Carbon;
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

class StockTrailTest extends MPSTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->user          = $this->createUser('super');
        $this->unit          = factory(Unit::class)->create();
        $this->brand         = factory(Brand::class)->create();
        $this->account       = factory(Account::class)->create();
        $category            = factory(Category::class)->create();
        $this->saleRoute     = url(module('route')) . '/app/sales/';
        $this->purchaseRoute = url(module('route')) . '/app/purchases/';
        $this->customer      = factory(Customer::class)->create(['user_id' => $this->user->id]);
        $this->supplier      = factory(Supplier::class)->create(['user_id' => $this->user->id]);
        $locations           = factory(Location::class, 2)->create(['account_id' => $this->account->id]);

        $this->locations = $locations;
        $previous_date   = now()->subMonths(2);
        Carbon::setTestNow($previous_date);
        factory('Modules\MPS\Models\Setting')->create(['mps_key' => 'stock', 'mps_value' => '1']);
        $this->items = factory(Item::class, 5)->create()->each(function ($item) use ($category, $locations) {
            $item->categories()->sync($category->id);
            foreach ($locations as $location) {
                session(['location_id' => $location->id]);
                $item->stock()->create(['quantity' => 20]);
            }
        });
    }

    public function testMPSStockTrailWithSalesAndPurchases()
    {
        // First date
        $first_date = now()->addDay();
        Carbon::setTestNow($first_date);
        $this->item     = $this->items->first();
        $this->location = $this->locations->first();
        session(['location_id' => $this->location->id]);

        $this->createPurchase(2, 10); // Buy 2 (20+2)
        $this->assertEquals(22, $this->item->stock()->first()->quantity);

        // Second date
        $second_date = now()->addDays(4);
        Carbon::setTestNow();
        Carbon::setTestNow($second_date);

        $this->createSale(4, 15); // Sell 4 (22-18)
        $this->assertEquals(18, $this->item->stock()->first()->quantity);

        // Third date
        $third_date = now()->addDays(6);
        Carbon::setTestNow();
        Carbon::setTestNow($third_date);

        $this->createPurchase(3, 10); // Buy 3 (18+3)
        $this->assertEquals(21, $this->item->stock()->first()->quantity);

        // Fourth date
        $fourth_date = now()->addDays(8);
        Carbon::setTestNow();
        Carbon::setTestNow($fourth_date);

        $this->createSale(2, 15); // Sell 4 (21-2)
        $this->assertEquals(19, $this->item->stock()->first()->quantity);

        // Set date to normal
        Carbon::setTestNow();

        // Test stock a previous date
        $this->assertEquals(now()->format('Y-m-d'), date('Y-m-d'));
        $this->assertEquals(22, $this->item->getStockOn($first_date));
        $this->assertEquals(18, $this->item->getStockOn($second_date));
        $this->assertEquals(21, $this->item->getStockOn($third_date));
        $this->assertEquals(19, $this->item->getStockOn($fourth_date));
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
