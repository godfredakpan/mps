<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Supplier;
use Modules\MPS\Models\Permission;
use Modules\MPS\Tests\MPSTestCase;

class SupplierPermissionsTest extends MPSTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->super = $this->createUser('super');
        $this->admin = $this->createUser('admin');
        $this->staff = $this->createUser('staff');
        $this->route = url(module('route')) . '/app/suppliers/';

        $this->suppliers = factory(Supplier::class, 5)->create(['user_id' => $this->admin->id]);
    }

    public function testMPS1SuperPermissionsForSupplierRoute()
    {
        $supplier = $this->suppliers->first();

        // can access the view route
        $this->actingAs($this->super)->ajax()->get($this->route . $supplier->id)->assertOk();

        // can access the store route
        $this->actingAs($this->super)->ajax()->post($this->route, [])->assertStatus(422);

        // can access the update route
        $this->actingAs($this->super)->ajax()->put($this->route . $supplier->id, [])->assertStatus(422);

        // can access update route of others
        $this->actingAs($this->super)->ajax()->put($this->route . $supplier->id, [])->assertStatus(422);

        // can delete
        $this->actingAs($this->super)->ajax()->delete($this->route . $supplier->id)->assertOk();
    }

    public function testMPS2SupplierRolePermissionsForStaffWithNoPermissions()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $supplier = factory(Supplier::class)->create(['user_id' => $this->staff->id]);
        $form     = factory(Supplier::class)->make(['user_id' => $this->staff->id])->toArray();

        $this->actingAs($this->staff)->ajax()->get($this->route . $supplier->id)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->get($this->route . '?limit=10&byColumn=false&orderBy=date+desc&page=1')->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->post($this->route, $form)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->put($this->route . $supplier->id, $form)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->delete($this->route . $supplier->id)->assertStatus(403);
    }

    public function testMPS3SupplierRolePermissionsForStaffWithPermissions()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        // supplier
        $role = $this->staff->roles()->first();
        Permission::firstOrCreate(['name' => 'read-suppliers'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'update-suppliers'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'create-suppliers'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'delete-suppliers'])->assignRole($role);

        $supplier = factory(Supplier::class)->create(['user_id' => $this->staff->id]);
        $others   = factory(Supplier::class)->create(['user_id' => $this->super->id]);
        $form     = factory(Supplier::class)->make(['user_id' => $this->staff->id])->toArray();

        $this->actingAs($this->staff)->ajax()->get($this->route . '?limit=10&byColumn=false&orderBy=date+desc&page=1')->assertOk();
        $this->actingAs($this->staff)->ajax()->get($this->route . $supplier->id)->assertOk();
        $this->actingAs($this->staff)->ajax()->post($this->route, $form)->assertOk();
        $this->actingAs($this->staff)->ajax()->put($this->route . $supplier->id, $form)->assertOk();
        $this->actingAs($this->staff)->ajax()->put($this->route . $others->id, $form)->assertStatus(404);
        $this->actingAs($this->staff)->ajax()->delete($this->route . $supplier->id)->assertOk();
    }

    public function testMPS4PublicCannotPerformAnyActionOnSupplier()
    {
        $this->ajax()->get($this->route)->assertUnauthorized();
        $this->ajax()->post($this->route, [])->assertUnauthorized();
        $this->ajax()->put($this->route . '1', [])->assertUnauthorized();
        $this->ajax()->delete($this->route . '1')->assertUnauthorized();
    }
}
