<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Customer;
use Modules\MPS\Models\Permission;
use Modules\MPS\Tests\MPSTestCase;

class CustomerPermissionsTest extends MPSTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->super = $this->createUser('super');
        $this->admin = $this->createUser('admin');
        $this->staff = $this->createUser('staff');
        $this->route = url(module('route')) . '/app/customers/';

        $this->customers = factory(Customer::class, 5)->create(['user_id' => $this->admin->id]);
    }

    public function testMPS1SuperPermissionsForCustomerRoute()
    {
        $customer = $this->customers->first();

        // can access the view route
        $this->actingAs($this->super)->ajax()->get($this->route . $customer->id)->assertOk();

        // can access the store route
        $this->actingAs($this->super)->ajax()->post($this->route, [])->assertStatus(422);

        // can access the update route
        $this->actingAs($this->super)->ajax()->put($this->route . $customer->id, [])->assertStatus(422);

        // can access update route of others
        $this->actingAs($this->super)->ajax()->put($this->route . $customer->id, [])->assertStatus(422);

        // can delete
        $this->actingAs($this->super)->ajax()->delete($this->route . $customer->id)->assertOk();
    }

    public function testMPS2CustomerRolePermissionsForStaffWithNoPermissions()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $customer = factory(Customer::class)->create(['user_id' => $this->staff->id]);
        $form     = factory(Customer::class)->make(['user_id' => $this->staff->id])->toArray();

        $this->actingAs($this->staff)->ajax()->get($this->route . $customer->id)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->get($this->route . '?limit=10&byColumn=false&orderBy=date+desc&page=1')->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->post($this->route, $form)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->put($this->route . $customer->id, $form)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->delete($this->route . $customer->id)->assertStatus(403);
    }

    public function testMPS3CustomerRolePermissionsForStaffWithPermissions()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        // customer
        $role = $this->staff->roles()->first();
        Permission::firstOrCreate(['name' => 'read-customers'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'update-customers'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'create-customers'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'delete-customers'])->assignRole($role);

        $customer = factory(Customer::class)->create(['user_id' => $this->staff->id]);
        $others   = factory(Customer::class)->create(['user_id' => $this->super->id]);
        $form     = factory(Customer::class)->make(['user_id' => $this->staff->id])->toArray();

        $this->actingAs($this->staff)->ajax()->get($this->route . '?limit=10&byColumn=false&orderBy=date+desc&page=1')->assertOk();
        $this->actingAs($this->staff)->ajax()->get($this->route . $customer->id)->assertOk();
        $this->actingAs($this->staff)->ajax()->post($this->route, $form)->assertOk();
        $this->actingAs($this->staff)->ajax()->put($this->route . $customer->id, $form)->assertOk();
        $this->actingAs($this->staff)->ajax()->put($this->route . $others->id, $form)->assertStatus(404);
        $this->actingAs($this->staff)->ajax()->delete($this->route . $customer->id)->assertOk();
    }

    public function testMPS4PublicCannotPerformAnyActionOnCustomer()
    {
        $this->ajax()->get($this->route)->assertUnauthorized();
        $this->ajax()->post($this->route, [])->assertUnauthorized();
        $this->ajax()->put($this->route . '1', [])->assertUnauthorized();
        $this->ajax()->delete($this->route . '1')->assertUnauthorized();
    }
}
