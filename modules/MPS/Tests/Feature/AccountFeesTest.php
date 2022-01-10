<?php

namespace Modules\MPS\Tests\Feature;

use Carbon\Carbon;
use Modules\MPS\Models\Account;
use Modules\MPS\Models\Payment;
use Modules\MPS\Models\Customer;
use Modules\MPS\Models\Location;
use Modules\MPS\Models\Supplier;
use Modules\MPS\Tests\MPSTestCase;

class AccountFeesTest extends MPSTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUser('super');
    }

    public function testAccountJournalTransactionFeesForBoth()
    {
        Carbon::setTestNow();
        Carbon::setTestNow(now()->subMinutes(10));
        $customer = factory(Customer::class)->create(['user_id' => $this->user->id]);
        $account  = factory(Account::class)->create([
            'opening_balance' => 0,
            'fees'            => 1,
            'fixed'           => 2,
            'percentage'      => 3.9,
            'apply_to'        => 'both',
        ]);
        $location = factory(Location::class)->create(['account_id' => $account->id]);
        session(['location_id' => $location->id]);
        $payment = $customer->payments()->create(
            factory(Payment::class)->make([
                'received'   => 1,
                'amount'     => 100,
                'gateway'    => 'cash',
                'account_id' => $account->id,
                'user_id'    => $this->user->id,
            ])->toArray()
        );
        $account->refresh();
        $this->assertTrue($account->journal()->exists());
        $this->assertEquals(2, $account->journal->transactions()->count());
        $this->assertEquals(
            round(10000 - (200 + ((10000 - 200) * 3.9 / 100))),
            $account->journal->balance->getAmount()
        );

        Carbon::setTestNow(now()->addMinutes(5));
        // $payment->update(['amount' => 80, 'updated_at' => now()]);
        $payment->updated_at = now();
        $payment->amount     = 80;
        $payment->save();
        $account->refresh();
        $this->assertEquals(6, $account->journal->transactions()->count());
        $this->assertEquals(
            round(8000 - (200 + ((8000 - 200) * 3.9 / 100))),
            $account->journal->balance->getAmount()
        );

        Carbon::setTestNow(now()->addMinutes(2));
        $payment->delete();
        $account->refresh();
        // dd($account->journal->transactions()->without('subject')->get()->toArray());
        $this->assertEquals(8, $account->journal->transactions()->count());
        $this->assertEquals(0, $account->journal->balance->getAmount());
    }

    public function testAccountJournalTransactionFeesForCredit()
    {
        Carbon::setTestNow();
        Carbon::setTestNow(now()->subMinutes(10));
        $customer = factory(Customer::class)->create(['user_id' => $this->user->id]);
        $supplier = factory(Supplier::class)->create(['user_id' => $this->user->id]);
        $account  = factory(Account::class)->create([
            'opening_balance' => 0,
            'fees'            => 1,
            'fixed'           => 2,
            'percentage'      => 3.9,
            'apply_to'        => 'in',
        ]);
        $location = factory(Location::class)->create(['account_id' => $account->id]);
        session(['location_id' => $location->id]);
        $payment = $customer->payments()->create(
            factory(Payment::class)->make([
                'received'   => 1,
                'amount'     => 100,
                'gateway'    => 'cash',
                'account_id' => $account->id,
                'user_id'    => $this->user->id,
            ])->toArray()
        );
        $account->refresh();
        $this->assertTrue($account->journal()->exists());
        $this->assertEquals(2, $account->journal->transactions()->count());
        $balance = $account->journal->balance->getAmount();
        $this->assertEquals(round(10000 - (200 + ((10000 - 200) * 3.9 / 100))), $balance);
        $supplier->payments()->create(
            factory(Payment::class)->make([
                'received'   => 1,
                'amount'     => 50,
                'gateway'    => 'cash',
                'account_id' => $account->id,
                'user_id'    => $this->user->id,
            ])->toArray()
        );
        $account->refresh();
        $this->assertTrue($account->journal()->exists());
        $this->assertEquals(3, $account->journal->transactions()->count());
        $this->assertEquals($balance - 5000, $account->journal->balance->getAmount());

        Carbon::setTestNow(now()->addMinutes(5));
        // $payment->update(['amount' => 80, 'updated_at' => now()]);
        $payment->updated_at = now();
        $payment->amount     = 80;
        $payment->save();
        $account->refresh();
        $this->assertEquals(7, $account->journal->transactions()->count());
        $this->assertEquals(
            round(3000 - (200 + ((8000 - 200) * 3.9 / 100))),
            $account->journal->balance->getAmount()
        );

        Carbon::setTestNow(now()->addMinutes(2));
        $payment->delete();
        $account->refresh();
        $this->assertEquals(9, $account->journal->transactions()->count());
        $this->assertEquals(-5000, $account->journal->balance->getAmount());
    }

    public function testAccountJournalTransactionFeesForDebit()
    {
        Carbon::setTestNow();
        Carbon::setTestNow(now()->subMinutes(10));
        $customer = factory(Customer::class)->create(['user_id' => $this->user->id]);
        $supplier = factory(Supplier::class)->create(['user_id' => $this->user->id]);
        $account  = factory(Account::class)->create([
            'opening_balance' => 0,
            'fees'            => 1,
            'fixed'           => 2,
            'percentage'      => 3.9,
            'apply_to'        => 'out',
        ]);
        $this->assertEquals(0, $account->refresh()->journal->balance->getAmount());
        $location = factory(Location::class)->create(['account_id' => $account->id]);
        session(['location_id' => $location->id]);
        $customer->payments()->create(
            factory(Payment::class)->make([
                'received'   => 1,
                'amount'     => 100,
                'gateway'    => 'cash',
                'account_id' => $account->id,
                'user_id'    => $this->user->id,
            ])->toArray()
        );
        $account->refresh();
        $this->assertTrue($account->journal()->exists());
        $this->assertEquals(1, $account->journal->transactions()->count());
        $balance = $account->journal->balance->getAmount();
        $this->assertEquals(10000, $balance);
        $payment = $supplier->payments()->create(
            factory(Payment::class)->make([
                'received'   => 1,
                'amount'     => 50,
                'gateway'    => 'cash',
                'account_id' => $account->id,
                'user_id'    => $this->user->id,
            ])->toArray()
        );
        $account->refresh();
        $this->assertTrue($account->journal()->exists());
        $this->assertEquals(3, $account->journal->transactions()->count());
        $this->assertEquals(round(5000 - (200 + ((5000 - 200) * 3.9 / 100))), $account->journal->balance->getAmount());

        Carbon::setTestNow(now()->addMinutes(5));
        // $payment->update(['amount' => 80, 'updated_at' => now()]);
        $payment->updated_at = now();
        $payment->amount     = 80;
        $payment->save();
        $account->refresh();
        $this->assertEquals(7, $account->journal->transactions()->count());
        $this->assertEquals(
            round(2000 - (200 + ((8000 - 200) * 3.9 / 100))),
            $account->journal->balance->getAmount()
        );

        Carbon::setTestNow(now()->addMinutes(2));
        $payment->delete();
        $account->refresh();
        $this->assertEquals(9, $account->journal->transactions()->count());
        $this->assertEquals(10000, $account->journal->balance->getAmount());
    }

    public function testAccountJournalTransactionFeesForNone()
    {
        $customer = factory(Customer::class)->create(['user_id' => $this->user->id]);
        $supplier = factory(Supplier::class)->create(['user_id' => $this->user->id]);
        $account  = factory(Account::class)->create([
            'opening_balance' => 0,
            'fees'            => 0,
            'fixed'           => 2,
            'percentage'      => 3.9,
            'apply_to'        => '',
        ]);
        $location = factory(Location::class)->create(['account_id' => $account->id]);
        session(['location_id' => $location->id]);
        $customer->payments()->create(
            factory(Payment::class)->make([
                'received'   => 1,
                'amount'     => 100,
                'gateway'    => 'cash',
                'account_id' => $account->id,
                'user_id'    => $this->user->id,
            ])->toArray()
        );
        $account->refresh();
        $this->assertTrue($account->journal()->exists());
        $this->assertEquals(1, $account->journal->transactions()->count());
        $balance = $account->journal->balance->getAmount();
        $this->assertEquals(10000, $balance);
        $supplier->payments()->create(
            factory(Payment::class)->make([
                'received'   => 1,
                'amount'     => 50,
                'gateway'    => 'cash',
                'account_id' => $account->id,
                'user_id'    => $this->user->id,
            ])->toArray()
        );
        $account->refresh();
        $this->assertTrue($account->journal()->exists());
        $this->assertEquals(2, $account->journal->transactions()->count());
        $this->assertEquals($balance - 5000, $account->journal->balance->getAmount());
    }
}
