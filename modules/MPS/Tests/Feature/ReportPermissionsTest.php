<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Item;
use Modules\MPS\Models\Account;
use Modules\MPS\Models\Category;
use Modules\MPS\Models\Customer;
use Modules\MPS\Models\Location;
use Modules\MPS\Models\Permission;
use Modules\MPS\Tests\MPSTestCase;

class ReportPermissionsTest extends MPSTestCase
{
    use TestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->super    = $this->createUser('super');
        $this->staff    = $this->createUser('staff');
        $this->account  = factory(Account::class)->create();
        $this->category = factory(Category::class)->create();
        $this->route    = url(module('route')) . '/app/reports/';
        $this->params   = '?limit=10&byColumn=false&orderBy=date+desc&page=1';
        $this->customer = factory(Customer::class)->create(['user_id' => $this->super->id]);
        $this->location = factory(Location::class)->create(['account_id' => $this->account->id]);
        session(['location_id' => $this->location->id]);
        // $this->item = factory(Item::class)->create();
        // $this->item->categories()->sync($this->category->id);
        // $this->item->stock()->create(['quantity' => 20]);
    }

    public function testMPS1SuperUserCanAccessReports()
    {
        // can access reports
        $this->actingAs($this->super)->ajax()->get($this->route . 'totals')->assertOk();
        // $this->actingAs($this->super)->ajax()->get('/app/logs' . $this->params)->assertOk();
        $this->actingAs($this->super)->ajax()->get($this->route . 'items' . $this->params)->assertOk();
        $this->actingAs($this->super)->ajax()->get($this->route . 'sales' . $this->params)->assertOk();
        $this->actingAs($this->super)->ajax()->get($this->route . 'taxes' . $this->params)->assertOk();
        $this->actingAs($this->super)->ajax()->get($this->route . 'incomes' . $this->params)->assertOk();
        $this->actingAs($this->super)->ajax()->get($this->route . 'expenses' . $this->params)->assertOk();
        $this->actingAs($this->super)->ajax()->get($this->route . 'payments' . $this->params)->assertOk();
        $this->actingAs($this->super)->ajax()->get($this->route . 'items/top' . $this->params)->assertOk();
        $this->actingAs($this->super)->ajax()->get($this->route . 'registers' . $this->params)->assertOk();
        $this->actingAs($this->super)->ajax()->get($this->route . 'purchases' . $this->params)->assertOk();
        $this->actingAs($this->super)->ajax()->get($this->route . 'adjustments' . $this->params)->assertOk();
        $this->actingAs($this->super)->ajax()->get($this->route . 'sales/table' . $this->params)->assertOk();
        $this->actingAs($this->super)->ajax()->get($this->route . 'time_clocks' . $this->params)->assertOk();
        $this->actingAs($this->super)->ajax()->get($this->route . 'incomes/table' . $this->params)->assertOk();
        $this->actingAs($this->super)->ajax()->get($this->route . 'expenses/table' . $this->params)->assertOk();
        $this->actingAs($this->super)->ajax()->get($this->route . 'payments/table' . $this->params)->assertOk();
        $this->actingAs($this->super)->ajax()->get($this->route . 'purchases/table' . $this->params)->assertOk();
        $this->actingAs($this->super)->ajax()->get($this->route . 'stock_transfers' . $this->params)->assertOk();
        $this->actingAs($this->super)->ajax()->get($this->route . 'adjustments/table' . $this->params)->assertOk();
        $this->actingAs($this->super)->ajax()->get($this->route . 'stock_transfers/table' . $this->params)->assertOk();
    }

    public function testMPS2StaffWithPermissionsCanAccessReports()
    {
        // staff with permissions can access reports
        $role = $this->staff->roles()->first();

        Permission::firstOrCreate(['name' => 'read-report'])->assignRole($role);
        $this->actingAs($this->staff)->ajax()->get($this->route . 'totals')->assertOk();
        // $this->actingAs($this->staff)->ajax()->get('/app/logs' . $this->params)->assertOk();

        Permission::firstOrCreate(['name' => 'items-report'])->assignRole($role);
        $this->actingAs($this->staff)->ajax()->get($this->route . 'items' . $this->params)->assertOk();
        $this->actingAs($this->staff)->ajax()->get($this->route . 'items/top' . $this->params)->assertOk();

        Permission::firstOrCreate(['name' => 'sales-report'])->assignRole($role);
        $this->actingAs($this->staff)->ajax()->get($this->route . 'sales' . $this->params)->assertOk();
        $this->actingAs($this->staff)->ajax()->get($this->route . 'sales/table' . $this->params)->assertOk();

        Permission::firstOrCreate(['name' => 'taxes-report'])->assignRole($role);
        $this->actingAs($this->staff)->ajax()->get($this->route . 'taxes' . $this->params)->assertOk();

        Permission::firstOrCreate(['name' => 'incomes-report'])->assignRole($role);
        $this->actingAs($this->staff)->ajax()->get($this->route . 'incomes' . $this->params)->assertOk();
        $this->actingAs($this->staff)->ajax()->get($this->route . 'incomes/table' . $this->params)->assertOk();

        Permission::firstOrCreate(['name' => 'expenses-report'])->assignRole($role);
        $this->actingAs($this->staff)->ajax()->get($this->route . 'expenses' . $this->params)->assertOk();
        $this->actingAs($this->staff)->ajax()->get($this->route . 'expenses/table' . $this->params)->assertOk();

        Permission::firstOrCreate(['name' => 'payments-report'])->assignRole($role);
        $this->actingAs($this->staff)->ajax()->get($this->route . 'payments' . $this->params)->assertOk();
        $this->actingAs($this->staff)->ajax()->get($this->route . 'payments/table' . $this->params)->assertOk();

        Permission::firstOrCreate(['name' => 'registers-report'])->assignRole($role);
        $this->actingAs($this->staff)->ajax()->get($this->route . 'registers' . $this->params)->assertOk();

        Permission::firstOrCreate(['name' => 'purchases-report'])->assignRole($role);
        $this->actingAs($this->staff)->ajax()->get($this->route . 'purchases' . $this->params)->assertOk();
        $this->actingAs($this->staff)->ajax()->get($this->route . 'purchases/table' . $this->params)->assertOk();

        Permission::firstOrCreate(['name' => 'stock-adjustments-report'])->assignRole($role);
        $this->actingAs($this->staff)->ajax()->get($this->route . 'adjustments' . $this->params)->assertOk();
        $this->actingAs($this->staff)->ajax()->get($this->route . 'adjustments/table' . $this->params)->assertOk();

        Permission::firstOrCreate(['name' => 'stock-transfers-report'])->assignRole($role);
        $this->actingAs($this->staff)->ajax()->get($this->route . 'stock_transfers' . $this->params)->assertOk();
        $this->actingAs($this->staff)->ajax()->get($this->route . 'stock_transfers/table' . $this->params)->assertOk();

        Permission::firstOrCreate(['name' => 'time-clock-report'])->assignRole($role);
        $this->actingAs($this->staff)->ajax()->get($this->route . 'time_clocks' . $this->params)->assertOk();
    }

    public function testMPS3StaffWithNoPermissionsCannotAccessReports()
    {
        // staff can't access reports
        $this->actingAs($this->staff)->ajax()->get($this->route . 'totals')->assertForbidden();
        // $this->actingAs($this->staff)->ajax()->get('/app/logs' . $this->params)->assertForbidden();
        $this->actingAs($this->staff)->ajax()->get($this->route . 'items' . $this->params)->assertForbidden();
        $this->actingAs($this->staff)->ajax()->get($this->route . 'sales' . $this->params)->assertForbidden();
        $this->actingAs($this->staff)->ajax()->get($this->route . 'taxes' . $this->params)->assertForbidden();
        $this->actingAs($this->staff)->ajax()->get($this->route . 'incomes' . $this->params)->assertForbidden();
        $this->actingAs($this->staff)->ajax()->get($this->route . 'expenses' . $this->params)->assertForbidden();
        $this->actingAs($this->staff)->ajax()->get($this->route . 'payments' . $this->params)->assertForbidden();
        $this->actingAs($this->staff)->ajax()->get($this->route . 'items/top' . $this->params)->assertForbidden();
        $this->actingAs($this->staff)->ajax()->get($this->route . 'registers' . $this->params)->assertForbidden();
        $this->actingAs($this->staff)->ajax()->get($this->route . 'purchases' . $this->params)->assertForbidden();
        $this->actingAs($this->staff)->ajax()->get($this->route . 'adjustments' . $this->params)->assertForbidden();
        $this->actingAs($this->staff)->ajax()->get($this->route . 'sales/table' . $this->params)->assertForbidden();
        $this->actingAs($this->staff)->ajax()->get($this->route . 'time_clocks' . $this->params)->assertForbidden();
        $this->actingAs($this->staff)->ajax()->get($this->route . 'incomes/table' . $this->params)->assertForbidden();
        $this->actingAs($this->staff)->ajax()->get($this->route . 'expenses/table' . $this->params)->assertForbidden();
        $this->actingAs($this->staff)->ajax()->get($this->route . 'payments/table' . $this->params)->assertForbidden();
        $this->actingAs($this->staff)->ajax()->get($this->route . 'purchases/table' . $this->params)->assertForbidden();
        $this->actingAs($this->staff)->ajax()->get($this->route . 'stock_transfers' . $this->params)->assertForbidden();
        $this->actingAs($this->staff)->ajax()->get($this->route . 'adjustments/table' . $this->params)->assertForbidden();
        $this->actingAs($this->staff)->ajax()->get($this->route . 'stock_transfers/table' . $this->params)->assertForbidden();
    }

    public function testMPS4PublicCannotAccessReports()
    {
        $this->ajax()->get($this->route . 'totals')->assertUnauthorized();
        // $this->ajax()->get('/app/logs' . $this->params)->assertUnauthorized();
        $this->ajax()->get($this->route . 'items' . $this->params)->assertUnauthorized();
        $this->ajax()->get($this->route . 'sales' . $this->params)->assertUnauthorized();
        $this->ajax()->get($this->route . 'taxes' . $this->params)->assertUnauthorized();
        $this->ajax()->get($this->route . 'incomes' . $this->params)->assertUnauthorized();
        $this->ajax()->get($this->route . 'expenses' . $this->params)->assertUnauthorized();
        $this->ajax()->get($this->route . 'payments' . $this->params)->assertUnauthorized();
        $this->ajax()->get($this->route . 'items/top' . $this->params)->assertUnauthorized();
        $this->ajax()->get($this->route . 'registers' . $this->params)->assertUnauthorized();
        $this->ajax()->get($this->route . 'purchases' . $this->params)->assertUnauthorized();
        $this->ajax()->get($this->route . 'adjustments' . $this->params)->assertUnauthorized();
        $this->ajax()->get($this->route . 'sales/table' . $this->params)->assertUnauthorized();
        $this->ajax()->get($this->route . 'time_clocks' . $this->params)->assertUnauthorized();
        $this->ajax()->get($this->route . 'incomes/table' . $this->params)->assertUnauthorized();
        $this->ajax()->get($this->route . 'expenses/table' . $this->params)->assertUnauthorized();
        $this->ajax()->get($this->route . 'payments/table' . $this->params)->assertUnauthorized();
        $this->ajax()->get($this->route . 'purchases/table' . $this->params)->assertUnauthorized();
        $this->ajax()->get($this->route . 'stock_transfers' . $this->params)->assertUnauthorized();
        $this->ajax()->get($this->route . 'adjustments/table' . $this->params)->assertUnauthorized();
        $this->ajax()->get($this->route . 'stock_transfers/table' . $this->params)->assertUnauthorized();
    }
}
