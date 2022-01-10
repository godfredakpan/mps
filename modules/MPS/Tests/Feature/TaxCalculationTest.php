<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Tax;
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

class TaxCalculationTest extends MPSTestCase
{
    use TestHelpers;

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
        $this->location      = factory(Location::class)->create(['account_id' => $this->account->id]);
        session(['location_id' => $this->location->id]);

        $this->vat_rate = 20;
        $this->vat[]    = factory(Tax::class)->create(['name' => 'VAT @ 20%', 'code' => 'vat@20', 'rate' => $this->vat_rate, 'compound' => false, 'state' => false, 'same' => false])->toArray()['id'];

        $this->gst_rate = 18;
        $this->taxes[]  = factory(Tax::class)->create(['name' => 'CGST @ 9%', 'code' => 'cgst@9', 'rate' => 9, 'compound' => false, 'state' => true, 'same' => true])->toArray()['id'];
        $this->taxes[]  = factory(Tax::class)->create(['name' => 'SGST @ 9%', 'code' => 'sgst@9', 'rate' => 9, 'compound' => false, 'state' => true, 'same' => true])->toArray()['id'];
        // $this->taxes[]  = factory(Tax::class)->create(['name' => 'IGST @ 18%', 'code' => 'igst@18', 'rate' => 18, 'compound' => false, 'recoverable' => true, 'state' => true, 'same' => false])->toArray()['id'];

        $this->in_item = factory(Item::class)->create(['tax_included' => 1, 'price' => 100, 'cost' => 60]);
        $this->in_item->taxes()->sync($this->vat);
        $this->in_item->categories()->sync($category->id);
        $this->in_item->stock()->create(['quantity' => 20, 'price' => 100, 'cost' => 60]);

        $this->ex_item = factory(Item::class)->create(['tax_included' => 0, 'price' => 100, 'cost' => 60]);
        $this->ex_item->taxes()->sync($this->vat);
        $this->ex_item->categories()->sync($category->id);
        $this->ex_item->stock()->create(['quantity' => 20, 'price' => 100, 'cost' => 60]);

        $this->in_item2 = factory(Item::class)->create(['tax_included' => 1, 'price' => 100, 'cost' => 60]);
        $this->in_item2->taxes()->sync($this->taxes);
        $this->in_item2->categories()->sync($category->id);
        $this->in_item2->stock()->create(['quantity' => 20, 'price' => 100, 'cost' => 60]);

        $this->ex_item2 = factory(Item::class)->create(['tax_included' => 0, 'price' => 100, 'cost' => 60]);
        $this->ex_item2->taxes()->sync($this->taxes);
        $this->ex_item2->categories()->sync($category->id);
        $this->ex_item2->stock()->create(['quantity' => 20, 'price' => 100, 'cost' => 60]);
    }

    public function testMPSExclusiveTaxIsWorkingFine()
    {
        // Single exclusive tax item
        $this->item = $this->ex_item;

        $res  = $this->createSale(1, $this->item->price);
        $sale = Sale::find($res['data']['id']);
        $this->assertEquals($this->item->price * (($this->vat_rate + 100) / 100), $sale->items()->first()->unit_price);

        $res      = $this->createPurchase(1, $this->item->cost);
        $purchase = Purchase::find($res['data']['id']);
        $this->assertEquals($this->item->cost * (($this->vat_rate + 100) / 100), $purchase->items()->first()->unit_cost);

        // Multiple exclusive taxes item
        $this->item = $this->ex_item2;

        $res  = $this->createSale(1, $this->item->price, 'taxes');
        $sale = Sale::find($res['data']['id']);
        $this->assertEquals($this->item->price * (($this->gst_rate + 100) / 100), $sale->items()->first()->unit_price);

        $res      = $this->createPurchase(1, $this->item->cost, 'taxes');
        $purchase = Purchase::find($res['data']['id']);
        $this->assertEquals($this->item->cost * (($this->gst_rate + 100) / 100), $purchase->items()->first()->unit_cost);
    }

    public function testMPSInclusiveTaxIsWorkingFine()
    {
        // Single inclusive tax item
        $this->item = $this->in_item;

        $res  = $this->createSale($this, 1, $this->user);
        $res  = $this->createSale(1, $this->item->price);
        $sale = Sale::find($res['data']['id']);
        $this->assertEquals($this->item->price, $sale->items()->first()->unit_price);

        $res      = $this->createPurchase(1, $this->item->cost);
        $purchase = Purchase::find($res['data']['id']);
        $this->assertEquals($this->item->cost, $purchase->items()->first()->unit_cost);

        // Multiple inclusive taxes item
        $this->item = $this->in_item2;

        $res  = $this->createSale(1, $this->item->price, 'taxes');
        $sale = Sale::find($res['data']['id']);
        $this->assertEquals($this->item->price, $sale->items()->first()->unit_price);

        $res      = $this->createPurchase(1, $this->item->cost, 'taxes');
        $purchase = Purchase::find($res['data']['id']);
        $this->assertEquals($this->item->cost, $purchase->items()->first()->unit_cost);
    }

    private function createPurchase($qty, $cost, $taxes = null, $expiry = null)
    {
        $purchase = factory(Purchase::class)->make([
            'draft'       => false,
            'supplier_id' => $this->supplier->id,
            'date'        => now()->format('Y-m-d'),
        ])->toArray();

        $item = factory(PurchaseItem::class)->make([
            'quantity'     => $qty,
            'cost'         => $cost,
            'net_cost'     => $cost,
            'expiry_date'  => $expiry,
            'item_id'      => $this->item->id,
            'code'         => $this->item->code,
            'name'         => $this->item->name,
            'tax_included' => $this->item->tax_included,
        ])->toArray();
        if ($taxes) {
            $item['taxes'] = $this->taxes;
        } else {
            $item['taxes'][] = $this->vat;
        }
        $purchase['items'][] = $item;

        $response = $this->actingAs($this->user)->ajax()->post($this->purchaseRoute, $purchase);
        return $response->json();
    }

    private function createSale($qty, $price, $taxes = null)
    {
        $sale = factory(Sale::class)->make([
            'draft'       => false,
            'customer_id' => $this->customer->id,
            'date'        => now()->format('Y-m-d'),
        ])->toArray();

        $item = factory(SaleItem::class)->make([
            'quantity'     => $qty,
            'price'        => $price,
            'net_price'    => $price,
            'item_id'      => $this->item->id,
            'code'         => $this->item->code,
            'name'         => $this->item->name,
            'tax_included' => $this->item->tax_included,
        ])->toArray();
        if ($taxes) {
            $item['taxes'] = $this->taxes;
        } else {
            $item['taxes'][] = $this->vat;
        }
        $sale['items'][] = $item;

        $response = $this->actingAs($this->user)->ajax()->post($this->saleRoute, $sale);
        return $response->json();
    }
}
