<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Item;
use Modules\MPS\Models\Account;
use Modules\MPS\Models\Category;
use Modules\MPS\Models\Customer;
use Modules\MPS\Models\Location;
use Modules\MPS\Models\Permission;
use Modules\MPS\Tests\MPSTestCase;

class ReturnOrderPermissionsTest extends MPSTestCase
{
    use TestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->super    = $this->createUser('super');
        $this->admin    = $this->createUser('admin');
        $this->staff    = $this->createUser('staff');
        $this->account  = factory(Account::class)->create();
        $this->category = factory(Category::class)->create();
        $this->route    = url(module('route')) . '/app/return_orders/';
        $this->customer = factory(Customer::class)->create(['user_id' => $this->super->id]);
        $this->location = factory(Location::class)->create(['account_id' => $this->account->id]);
        session(['location_id' => $this->location->id]);
        $this->item = factory(Item::class)->create();
        $this->item->categories()->sync($this->category->id);
        $this->item->stock()->create(['quantity' => 20]);

        $this->return_orders = $this->createReturnOrder($this, 2, 'sale', $this->super);
    }

    public function testMPS1SuperPermissionsForReturnOrderRoute()
    {
        $return_order = $this->return_orders->first();

        // can access the view route
        $this->actingAs($this->super)->ajax()->get($this->route . $return_order->id)->assertOk();

        // can access the store route
        $this->actingAs($this->super)->ajax()->post($this->route, [])->assertStatus(422);

        // can access the update route
        $this->actingAs($this->super)->ajax()->put($this->route . $return_order->id, [])->assertStatus(422);

        // can access update route of others
        $this->actingAs($this->super)->ajax()->put($this->route . $return_order->id, [])->assertStatus(422);

        // can delete
        $this->actingAs($this->super)->ajax()->delete($this->route . $return_order->id)->assertOk();
    }

    public function testMPS2ReturnOrderRolePermissionsForStaffWithNoPermissions()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $return_order = $this->createReturnOrder($this, 1, 'sale', $this->staff);
        $form         = $this->createReturnOrderForm($this, 'sale', $this->staff);

        $this->actingAs($this->staff)->ajax()->get($this->route . $return_order->id)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->get($this->route . '?limit=10&byColumn=false&orderBy=date+desc&page=1')->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->post($this->route, $form)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->put($this->route . $return_order->id, $form)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->delete($this->route . $return_order->id)->assertStatus(403);
    }

    public function testMPS3ReturnOrderRolePermissionsForStaffWithPermissions()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        // return-orders
        $role = $this->staff->roles()->first();
        Permission::firstOrCreate(['name' => 'read-return-orders'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'create-return-orders'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'update-return-orders'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'delete-return-orders'])->assignRole($role);

        $return_order = $this->createReturnOrder($this, 1, 'sale', $this->staff);
        $others       = $this->createReturnOrder($this, 1, 'sale', $this->super);
        $form         = $this->createReturnOrderForm($this, 'sale', $this->staff);

        $this->actingAs($this->staff)->ajax()->get($this->route . '?limit=10&byColumn=false&orderBy=date+desc&page=1')->assertOk();
        $this->actingAs($this->staff)->ajax()->get($this->route . $return_order->id)->assertOk();
        $this->actingAs($this->staff)->ajax()->post($this->route, $form)->assertOk();
        $this->actingAs($this->staff)->ajax()->put($this->route . $return_order->id, $form)->assertOk();
        $this->actingAs($this->staff)->ajax()->delete($this->route . $return_order->id)->assertOk();

        // Can't update or delete other users record
        $this->actingAs($this->staff)->ajax()->put($this->route . $others->id, $form)->assertStatus(404);
        $this->actingAs($this->staff)->ajax()->delete($this->route . $others->id)->assertStatus(404);
    }

    public function testMPS4PublicCannotPerformAnyActionOnReturnOrder()
    {
        $this->ajax()->get($this->route)->assertUnauthorized();
        $this->ajax()->post($this->route, [])->assertUnauthorized();
        $this->ajax()->put($this->route . '1', [])->assertUnauthorized();
        $this->ajax()->delete($this->route . '1')->assertUnauthorized();
    }
}
