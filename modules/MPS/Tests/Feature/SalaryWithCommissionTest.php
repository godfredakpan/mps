<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Item;
use Modules\MPS\Models\Salary;
use Modules\MPS\Models\Account;
use Modules\MPS\Models\Category;
use Modules\MPS\Models\Location;
use Modules\MPS\Events\SaleEvent;
use Modules\MPS\Tests\MPSTestCase;

class SalaryWithCommissionTest extends MPSTestCase
{
    use TestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->super    = $this->createUser('super');
        $this->account  = factory(Account::class)->create(['opening_balance' => 100]);
        $this->location = factory(Location::class)->create(['account_id' => $this->account->id]);
        $this->route    = url(module('route')) . '/app/salaries/';

        factory('Modules\MPS\Models\Setting')->create(['mps_key' => 'default_account', 'mps_value' => $this->account->id]);
        factory('Modules\MPS\Models\Setting')->create(['mps_key' => 'loyalty', 'mps_value' => json_encode(['staff' => ['spent' => 100, 'points' => 1], 'customer' => ['spent' => 100, 'points' => 2]])]);
    }

    public function testCommissionPointsAndAccountBalance()
    {
        $staff = $this->createUser('staff');
        $staff->meta()->updateOrCreate(['meta_key' => 'salary'], ['meta_value' => 1000]);
        $category = factory(Category::class)->create();
        session(['location_id' => $this->location->id]);
        $this->item = factory(Item::class)->create(['price' => 1000]);
        $this->item->categories()->sync($category->id);
        $this->item->stock()->create(['quantity' => 20]);
        $sales = $this->createSale($this, 2, $staff, 1, 1000);
        foreach ($sales as $sale) {
            event(new SaleEvent($sale, 'created'));
        }

        $this->assertEquals(20, usermeta($staff->id, 'points'));
        $this->assertEquals(40, $sales[0]->customer->refresh()->points);
        $this->assertEquals(10000, $this->account->refresh()->journal->balance->getAmount());

        $salary = factory(Salary::class)->make([
            'advance'    => 0,
            'points'     => 20,
            'amount'     => 20,
            'status'     => 'paid',
            'type'       => 'commission',
            'user_id'    => $staff->id,
            'account_id' => $this->account->id,
            'date'       => now()->format('Y-m-d'),
        ])->toArray();

        $response = $this->actingAs($this->super)->ajax()->post($this->route, $salary);
        // dd($response->json());
        $response->assertOk();
        $this->assertEquals(0, usermeta($staff->id, 'points'));
        $this->assertEquals(8000, $this->account->refresh()->journal->balance->getAmount());

        $salary           = $response['data'];
        $salary['points'] = 10;
        $salary['amount'] = 10;
        $response         = $this->actingAs($this->super)->ajax()->put($this->route . $response['data']['id'], $salary);
        $response->assertOk();
        $this->assertEquals(10, usermeta($staff->id, 'points'));
        $this->assertEquals(9000, $this->account->refresh()->journal->balance->getAmount());

        $this->actingAs($this->super)->ajax()->delete($this->route . $response['data']['id']);
        $response->assertOk();
        $this->assertEquals(20, usermeta($staff->id, 'points'));
        $this->assertEquals(10000, $this->account->refresh()->journal->balance->getAmount());
    }

    public function testSalaryAndAccountBalance()
    {
        $staff   = $this->createUser('staff');
        $account = factory(Account::class)->create(['opening_balance' => 1000]);
        $staff->meta()->updateOrCreate(['meta_key' => 'salary'], ['meta_value' => 1000]);
        $category = factory(Category::class)->create();
        session(['location_id' => $this->location->id]);
        $this->item = factory(Item::class)->create(['price' => 1000]);
        $this->item->categories()->sync($category->id);
        $this->item->stock()->create(['quantity' => 20]);
        $sales = $this->createSale($this, 2, $staff, 1, 1000);
        foreach ($sales as $sale) {
            event(new SaleEvent($sale, 'created'));
        }

        $this->assertEquals(100000, $account->refresh()->journal->balance->getAmount());

        $salary = factory(Salary::class)->make([
            'advance'    => 1,
            'amount'     => 200,
            'status'     => 'due',
            'type'       => 'salary',
            'user_id'    => $staff->id,
            'account_id' => $account->id,
            'date'       => now()->format('Y-m-d'),
        ])->toArray();

        $response = $this->actingAs($this->super)->ajax()->post($this->route, $salary);
        // dd($response->json());
        $response->assertOk();
        $this->assertEquals(100000, $account->refresh()->journal->balance->getAmount());

        $salary           = $response['data'];
        $salary['status'] = 'paid';
        $response         = $this->actingAs($this->super)->ajax()->put($this->route . $response['data']['id'], $salary);
        $response->assertOk();
        $this->assertEquals(80000, $account->refresh()->journal->balance->getAmount());

        $salary           = $response['data'];
        $salary['status'] = 'paid';
        $salary['amount'] = 300;
        $response         = $this->actingAs($this->super)->ajax()->put($this->route . $response['data']['id'], $salary);
        $response->assertOk();
        $this->assertEquals(70000, $account->refresh()->journal->balance->getAmount());

        $this->actingAs($this->super)->ajax()->delete($this->route . $response['data']['id']);
        $response->assertOk();
        $this->assertEquals(100000, $account->refresh()->journal->balance->getAmount());
    }
}
