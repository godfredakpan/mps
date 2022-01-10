<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Tax;
use Modules\MPS\Models\Item;
use Modules\MPS\Models\Unit;
use Modules\MPS\Models\Brand;
use Modules\MPS\Models\Account;
use Modules\MPS\Models\Payment;
use Modules\MPS\Models\Category;
use Modules\MPS\Models\Location;
use Modules\MPS\Events\SaleEvent;
use Modules\MPS\Tests\MPSTestCase;
use Modules\MPS\Events\PurchaseEvent;

class PaymentTest extends MPSTestCase
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
        $this->route   = url(module('route')) . '/app/payments/';
        $locations     = factory(Location::class, 2)->create(['account_id' => $this->account->id]);

        $taxes[]         = factory(Tax::class)->create(['name' => 'CGST @ 9%', 'code' => 'cgst@9', 'rate' => 9, 'compound' => false, 'state' => true, 'same' => true])->toArray()['id'];
        $taxes[]         = factory(Tax::class)->create(['name' => 'SGST @ 9%', 'code' => 'sgst@9', 'rate' => 9, 'compound' => false, 'state' => true, 'same' => true])->toArray()['id'];
        $taxes[]         = factory(Tax::class)->create(['name' => 'IGST @ 18%', 'code' => 'igst@18', 'rate' => 18, 'compound' => false, 'recoverable' => true, 'state' => true, 'same' => false])->toArray()['id'];
        $this->locations = $locations;
        $this->items     = factory(Item::class, 5)->create(['cost' => 50, 'price' => 100])
            ->each(function ($item) use ($category, $locations, $taxes) {
                $item->taxes()->sync($taxes);
                $item->categories()->sync($category->id);
                foreach ($locations as $location) {
                    session(['location_id' => $location->id]);
                    $item->stock()->create(['quantity' => 20]);
                }
            });
    }

    public function testMPSPaymentValidation()
    {
        $response = $this->actingAs($this->user)->ajax()->post($this->route, []);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
        $response->assertJsonValidationErrors(['customer_id', 'supplier_id', 'amount', 'account_id']);
    }

    public function testMPSPurchasePaymentWithoutPurchaseId()
    {
        // Check with draft true
        $this->item = $this->items->first();
        $location   = $this->locations->first();
        session(['location_id' => $location->id]);
        $purchases = $this->createPurchase($this, 2, $this->user, 1, 50);
        foreach ($purchases as $purchase) {
            event(new PurchaseEvent($purchase, 'created'));
        }
        $purchase = $purchases->first();
        $this->assertEquals(10000, $purchase->supplier->journal->balance->getAmount());
        $payment = factory('Modules\MPS\Models\Payment')->make([
            'received'    => 1,
            'gateway'     => 'cash',
            'user_id'     => $purchase->user_id,
            'account_id'  => $this->account->id,
            'supplier_id' => $purchase->supplier_id,
            'amount'      => $purchase->grand_total * 2,
        ])->toArray();
        $response = $this->actingAs($this->user)->ajax()->post($this->route, $payment);
        // dd($response->json());
        $response->assertOk();
        foreach ($purchases as $purchase) {
            $this->assertEquals(1, $purchase->refresh()->paid);
        }
        $this->assertEquals(0, $purchase->supplier->journal->balance->getAmount());

        // update
        $payment                 = Payment::latest()->first();
        $update                  = $payment->toArray();
        $update['supplier_id']   = $payment->payable->id;
        $update['gateway']       = 'cheque';
        $update['cheque_number'] = '123456';
        $response                = $this->actingAs($this->user)->ajax()->put($this->route . $payment->id, $update);
        // dd($response->json());
        $response->assertOk();

        $payment->refresh();
        $this->assertSame($update['gateway'], $payment->gateway);
        $this->assertSame($update['supplier_id'], $payment->payable->id);
        $this->assertSame($update['cheque_number'], $payment->cheque_number);
    }

    public function testMPSPurchasePaymentWithPurchaseId()
    {
        // Check with draft true
        $this->item = $this->items->first();
        $location   = $this->locations->first();
        session(['location_id' => $location->id]);
        $purchases = $this->createPurchase($this, 2, $this->user, 1, 50);
        foreach ($purchases as $purchase) {
            event(new PurchaseEvent($purchase, 'created'));
        }
        $purchase = $purchases->first();
        $this->assertEquals(10000, $purchase->supplier->journal->balance->getAmount());

        $payment = factory('Modules\MPS\Models\Payment')->make([
            'received'    => 1,
            'gateway'     => 'cash',
            'purchase_id' => $purchase->id,
            'user_id'     => $purchase->user_id,
            'account_id'  => $this->account->id,
            'supplier_id' => $purchase->supplier_id,
            'amount'      => $purchase->grand_total,
        ])->toArray();
        $response = $this->actingAs($this->user)->ajax()->post($this->route, $payment);
        // dd($response->json());
        $response->assertOk();
        $this->assertEquals(1, $purchase->refresh()->paid);
        $this->assertEquals(10000 / 2, $purchase->supplier->journal->balance->getAmount());
    }

    public function testMPSSalePaymentWithoutSaleId()
    {
        // Check with draft true
        $this->item = $this->items->first();
        $location   = $this->locations->first();
        session(['location_id' => $location->id]);
        $sales = $this->createSale($this, 2, $this->user, 1, 100);
        foreach ($sales as $sale) {
            event(new SaleEvent($sale, 'created'));
        }
        $sale = $sales->first();
        $this->assertEquals(20000, $sale->customer->journal->balance->getAmount());

        $payment = factory('Modules\MPS\Models\Payment')->make([
            'received'    => 1,
            'gateway'     => 'cash',
            'user_id'     => $sale->user_id,
            'account_id'  => $this->account->id,
            'customer_id' => $sale->customer_id,
            'amount'      => $sale->grand_total * 2,
        ])->toArray();
        $response = $this->actingAs($this->user)->ajax()->post($this->route, $payment);
        // dd($response->json());
        $response->assertOk();
        foreach ($sales as $sale) {
            $this->assertEquals(1, $sale->refresh()->paid);
        }
        $this->assertEquals(0, $sale->customer->journal->balance->getAmount());
    }

    public function testMPSSalePaymentWithSaleId()
    {
        // Check with draft true
        $this->item = $this->items->first();
        $location   = $this->locations->first();
        session(['location_id' => $location->id]);
        $sales = $this->createSale($this, 2, $this->user, 1, 100);
        foreach ($sales as $sale) {
            event(new SaleEvent($sale, 'created'));
        }
        $sale = $sales->first();
        $this->assertEquals(20000, $sale->customer->journal->balance->getAmount());

        $payment = factory('Modules\MPS\Models\Payment')->make([
            'received'    => 1,
            'gateway'     => 'cash',
            'sale_id'     => $sale->id,
            'user_id'     => $sale->user_id,
            'account_id'  => $this->account->id,
            'customer_id' => $sale->customer_id,
            'amount'      => $sale->grand_total,
        ])->toArray();
        $response = $this->actingAs($this->user)->ajax()->post($this->route, $payment);
        // dd($response->json());
        $response->assertOk();
        $this->assertEquals(1, $sale->refresh()->paid);
        $this->assertEquals(20000 / 2, $sale->customer->journal->balance->getAmount());
    }
}
