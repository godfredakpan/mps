<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Item;
use Modules\MPS\Models\Account;
use Modules\MPS\Models\Payment;
use Modules\MPS\Models\Category;
use Modules\MPS\Models\Customer;
use Modules\MPS\Models\Location;
use Modules\MPS\Models\Supplier;
use Modules\MPS\Events\SaleEvent;
use Modules\MPS\Tests\MPSTestCase;
use Modules\MPS\Events\PurchaseEvent;

class AccountTransactionTest extends MPSTestCase
{
    use TestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user     = $this->createUser('super');
        $category       = factory(Category::class)->create();
        $this->account  = factory(Account::class)->create(['opening_balance' => 0]);
        $locations      = factory(Location::class, 2)->create(['account_id' => $this->account->id]);
        $this->location = $locations->first();
        $this->items    = factory(Item::class, 5)->create()->each(function ($item) use ($category, $locations) {
            $item->categories()->sync($category->id);
            foreach ($locations as $location) {
                session(['location_id' => $location->id]);
                $item->stock()->create(['quantity' => 20]);
            }
        });
    }

    public function testCustomerPayments()
    {
        // insert
        $customer = factory(Customer::class)->create(['user_id' => $this->user->id]);
        $payment  = $customer->payments()->create(factory(Payment::class)->make([
            'received'    => 1,
            'amount'      => 100,
            'gateway'     => 'cash',
            'user_id'     => $this->user->id,
            'account_id'  => $this->account->id,
            'location_id' => $this->location->id,
        ])->toArray());

        $customer->refresh();
        $this->account->refresh();
        $this->assertTrue($this->account->journal()->exists());
        $this->assertEquals(2, $customer->journal->transactions()->count());
        $this->assertEquals(1, $this->account->journal->transactions()->count());
        $this->assertEquals($payment->amount * 100, $this->account->journal->balance->getAmount());
        $this->assertEquals(0 - ($payment->amount * 100), $customer->journal->balance->getAmount());

        // create sales for customer
        $this->item = $this->items->first();
        $sales      = $this->createSale($this, 2, $this->user, 1, 10, null, $customer);
        foreach ($sales as $sale) {
            event(new SaleEvent($sale, 'created'));
        }

        // update
        $payment->fill(['amount' => 50])->save();

        $payment->refresh();
        $customer->refresh();
        $this->account->refresh();
        $this->assertEquals(6, $customer->journal->transactions()->count());
        $this->assertEquals(3, $this->account->journal->transactions()->count());
        $this->assertEquals($payment->amount * 100, $this->account->journal->balance->getAmount());
        $this->assertEquals(0 - (($payment->amount - 20) * 100), $customer->journal->balance->getAmount());

        // delete
        $payment->delete();

        $customer->refresh();
        $this->account->refresh();
        $this->assertEquals(2000, $customer->journal->balance->getAmount());
        $this->assertEquals(7, $customer->journal->transactions()->count());
        $this->assertEquals(0, $this->account->journal->balance->getAmount());
        $this->assertEquals(4, $this->account->journal->transactions()->count());

        // delete sale
        $sales->first()->delete();

        $customer->refresh();
        $this->assertEquals(1000, $customer->journal->balance->getAmount());
        $this->assertEquals(8, $customer->journal->transactions()->count());

        // create another payment
        $payment = $customer->payments()->create(factory(Payment::class)->make([
            'received'    => 1,
            'amount'      => 50,
            'gateway'     => 'cash',
            'user_id'     => $this->user->id,
            'account_id'  => $this->account->id,
            'location_id' => $this->location->id,
        ])->toArray());

        $customer->refresh();
        $this->account->refresh();
        $this->assertEquals(9, $customer->journal->transactions()->count());
        $this->assertEquals(-4000, $customer->journal->balance->getAmount());
        $this->assertEquals(5, $this->account->journal->transactions()->count());
        $this->assertEquals($payment->amount * 100, $this->account->journal->balance->getAmount());
    }

    public function testSupplierPayments()
    {
        // insert
        $supplier = factory(Supplier::class)->create(['user_id' => $this->user->id]);
        $payment  = $supplier->payments()->create(factory(Payment::class)->make([
            'received'    => 1,
            'amount'      => 100,
            'gateway'     => 'cash',
            'user_id'     => $this->user->id,
            'account_id'  => $this->account->id,
            'location_id' => $this->location->id,
        ])->toArray());

        $supplier->refresh();
        $this->account->refresh();
        $this->assertTrue($this->account->journal()->exists());
        $this->assertEquals(2, $supplier->journal->transactions()->count());
        $this->assertEquals(1, $this->account->journal->transactions()->count());
        $this->assertEquals(0 - ($payment->amount * 100), $supplier->journal->balance->getAmount());
        $this->assertEquals(0 - ($payment->amount * 100), $this->account->journal->balance->getAmount());

        // create purchases for supplier
        $this->item = $this->items->first();
        $purchases  = $this->createPurchase($this, 2, $this->user, 1, 10, $supplier);
        foreach ($purchases as $purchase) {
            event(new PurchaseEvent($purchase, 'created'));
        }

        // update
        $payment->fill(['amount' => 50])->save();

        $payment->refresh();
        $supplier->refresh();
        $this->account->refresh();
        $this->assertEquals(6, $supplier->journal->transactions()->count());
        $this->assertEquals(3, $this->account->journal->transactions()->count());
        $this->assertEquals(0 - ($payment->amount * 100), $this->account->journal->balance->getAmount());
        $this->assertEquals(0 - (($payment->amount - 20) * 100), $supplier->journal->balance->getAmount());

        // delete
        $payment->delete();

        $supplier->refresh();
        $this->account->refresh();
        $this->assertEquals(2000, $supplier->journal->balance->getAmount());
        $this->assertEquals(7, $supplier->journal->transactions()->count());
        $this->assertEquals(0, $this->account->journal->balance->getAmount());
        $this->assertEquals(4, $this->account->journal->transactions()->count());

        // delete purchase
        $purchases->first()->delete();

        $supplier->refresh();
        $this->assertEquals(1000, $supplier->journal->balance->getAmount());
        $this->assertEquals(8, $supplier->journal->transactions()->count());

        // create another payment
        $payment = $supplier->payments()->create(factory(Payment::class)->make([
            'received'    => 1,
            'amount'      => 50,
            'gateway'     => 'cash',
            'user_id'     => $this->user->id,
            'account_id'  => $this->account->id,
            'location_id' => $this->location->id,
        ])->toArray());

        $supplier->refresh();
        $this->account->refresh();
        $this->assertEquals(9, $supplier->journal->transactions()->count());
        $this->assertEquals(-4000, $supplier->journal->balance->getAmount());
        $this->assertEquals(5, $this->account->journal->transactions()->count());
        $this->assertEquals(0 - ($payment->amount * 100), $this->account->journal->balance->getAmount());
    }
}
