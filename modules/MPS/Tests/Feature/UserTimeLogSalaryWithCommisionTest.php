<?php

namespace Modules\MPS\Tests\Feature;

use Carbon\Carbon;
use Modules\MPS\Models\Item;
use Modules\MPS\Models\Account;
use Modules\MPS\Models\Category;
use Modules\MPS\Models\Location;
use Modules\MPS\Events\SaleEvent;
use Modules\MPS\Models\TimeClock;
use Modules\MPS\Tests\MPSTestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class UserTimeLogSalaryWithCommisionTest extends MPSTestCase
{
    use TestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->super    = $this->createUser('super');
        $this->account  = factory(Account::class)->create();
        $this->location = factory(Location::class)->create(['account_id' => $this->account->id]);

        factory('Modules\MPS\Models\Setting')->create(['mps_key' => 'loyalty', 'mps_value' => json_encode(['staff' => ['spent' => 100, 'points' => 1], 'customer' => ['spent' => 100, 'points' => 2]])]);
    }

    public function testUserCommission()
    {
        $staff    = $this->createUser('staff');
        $category = factory(Category::class)->create();
        session(['location_id' => $this->location->id]);
        $this->item = factory(Item::class)->create(['price' => 100]);
        $this->item->categories()->sync($category->id);
        $this->item->stock()->create(['quantity' => 20]);
        $sales = $this->createSale($this, 2, $staff, 1, 100);
        foreach ($sales as $sale) {
            event(new SaleEvent($sale, 'created'));
        }

        $this->assertEquals(2, usermeta($staff->id, 'points'));
        $this->assertEquals(4, $sales[0]->customer->refresh()->points);
    }

    public function testUserTimeLogsAndSalary()
    {
        $staff = $this->createUser('staff');
        Auth::login($this->super);
        session(['location_id' => $this->location->id]);
        $staff->meta()->updateOrCreate(['meta_key' => 'clock_in', 'meta_value' => 'login']);
        $staff->meta()->updateOrCreate(['meta_key' => 'hourly_rate', 'meta_value' => 10]);
        Auth::logout();
        Carbon::setTestNow(now()->subHours(20));
        $this->ajax()->post('/login', ['username' => 'staff', 'password' => '123456'])->assertOk();
        Carbon::setTestNow(now()->addHours(8));
        $this->ajax()->actingAs($staff)->delete('/logout')->assertOk();
        Carbon::setTestNow();
        $this->assertEquals(1, TimeClock::count());

        session(['location_id' => $this->location->id]);
        Carbon::setTestNow(now()->subHours(10));
        $this->ajax()->post('/login', ['username' => 'staff', 'password' => '123456'])->assertOk();
        Carbon::setTestNow(now()->addHours(8));
        $this->ajax()->actingAs($staff)->delete('/logout')->assertOk();
        Carbon::setTestNow();
        $this->assertEquals(2, TimeClock::count());
        Config::set('activitylog.enabled', false);
        // dd(TimeClock::all()->toArray()); // same month
        $this->assertEquals(160, $staff->workHoursSalary(now()));
    }
}
