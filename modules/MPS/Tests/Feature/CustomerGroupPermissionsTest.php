<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Permission;
use Modules\MPS\Tests\MPSTestCase;
use Modules\MPS\Models\CustomerGroup;

class CustomerGroupPermissionsTest extends MPSTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->super = $this->createUser('super');
        $this->admin = $this->createUser('admin');
        $this->staff = $this->createUser('staff');
        $this->route = url(module('route')) . '/app/customer_groups/';

        $this->customer_groups = factory(CustomerGroup::class, 5)->create();
    }

    public function testMPS1SuperPermissionsForCustomerGroupRoute()
    {
        $customer_group = $this->customer_groups->first();

        // can access the view route
        $this->actingAs($this->super)->ajax()->get($this->route . $customer_group->id)->assertOk();

        // can access the store route
        $this->actingAs($this->super)->ajax()->post($this->route, [])->assertStatus(422);

        // can access the update route
        $this->actingAs($this->super)->ajax()->put($this->route . $customer_group->id, [])->assertStatus(422);

        // can access update route of others
        $this->actingAs($this->super)->ajax()->put($this->route . $customer_group->id, [])->assertStatus(422);

        // can delete
        $this->actingAs($this->super)->ajax()->delete($this->route . $customer_group->id)->assertOk();
    }

    public function testMPS2CustomerGroupRolePermissionsForStaffWithNoPermissions()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $customer_group = factory(CustomerGroup::class)->create();
        $form           = factory(CustomerGroup::class)->make()->toArray();

        $this->actingAs($this->staff)->ajax()->get($this->route . $customer_group->id)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->get($this->route . '?limit=10&byColumn=false&orderBy=date+desc&page=1')->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->post($this->route, $form)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->put($this->route . $customer_group->id, $form)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->delete($this->route . $customer_group->id)->assertStatus(403);
    }

    public function testMPS3CustomerGroupRolePermissionsForStaffWithPermissions()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        // customer-groups
        $role = $this->staff->roles()->first();
        Permission::firstOrCreate(['name' => 'read-customer-groups'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'update-customer-groups'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'create-customer-groups'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'delete-customer-groups'])->assignRole($role);

        $others         = $this->customer_groups->first();
        $customer_group = factory(CustomerGroup::class)->create();
        $form           = factory(CustomerGroup::class)->make()->toArray();
        $form2          = factory(CustomerGroup::class)->make()->toArray();
        $form3          = factory(CustomerGroup::class)->make()->toArray();

        $this->actingAs($this->staff)->ajax()->get($this->route . '?limit=10&byColumn=false&orderBy=date+desc&page=1')->assertOk();
        $this->actingAs($this->staff)->ajax()->get($this->route . $customer_group->id)->assertOk();
        $this->actingAs($this->staff)->ajax()->post($this->route, $form)->assertOk();
        $this->actingAs($this->staff)->ajax()->put($this->route . $customer_group->id, $form2)->assertOk();
        $this->actingAs($this->staff)->ajax()->delete($this->route . $customer_group->id)->assertOk();

        // Can update and delete customer_groups added by other users
        $this->actingAs($this->staff)->ajax()->put($this->route . $others->id, $form3)->assertOk();
        $this->actingAs($this->staff)->ajax()->delete($this->route . $others->id)->assertOk();
    }

    public function testMPS4PublicCannotPerformAnyActionOnCustomerGroup()
    {
        $this->ajax()->get($this->route)->assertUnauthorized();
        $this->ajax()->post($this->route, [])->assertUnauthorized();
        $this->ajax()->put($this->route . '1', [])->assertUnauthorized();
        $this->ajax()->delete($this->route . '1')->assertUnauthorized();
    }
}
