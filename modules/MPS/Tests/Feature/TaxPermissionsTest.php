<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Tax;
use Modules\MPS\Models\Permission;
use Modules\MPS\Tests\MPSTestCase;

class TaxPermissionsTest extends MPSTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->super = $this->createUser('super');
        $this->admin = $this->createUser('admin');
        $this->staff = $this->createUser('staff');
        $this->route = url(module('route')) . '/app/taxes/';

        $this->taxes = factory(Tax::class, 5)->create();
    }

    public function testMPS1SuperPermissionsForTaxRoute()
    {
        $tax = $this->taxes->first();

        // can access the view route
        $this->actingAs($this->super)->ajax()->get($this->route . $tax->id)->assertOk();

        // can access the store route
        $this->actingAs($this->super)->ajax()->post($this->route, [])->assertStatus(422);

        // can access the update route
        $this->actingAs($this->super)->ajax()->put($this->route . $tax->id, [])->assertStatus(422);

        // can access update route of others
        $this->actingAs($this->super)->ajax()->put($this->route . $tax->id, [])->assertStatus(422);

        // can delete
        $this->actingAs($this->super)->ajax()->delete($this->route . $tax->id)->assertOk();
    }

    public function testMPS2TaxRolePermissionsForStaffWithNoPermissions()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $tax  = factory(Tax::class)->create();
        $form = factory(Tax::class)->make()->toArray();

        $this->actingAs($this->staff)->ajax()->get($this->route . $tax->id)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->get($this->route . '?limit=10&byColumn=false&orderBy=date+desc&page=1')->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->post($this->route, $form)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->put($this->route . $tax->id, $form)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->delete($this->route . $tax->id)->assertStatus(403);
    }

    public function testMPS3TaxRolePermissionsForStaffWithPermissions()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        // tax
        $role = $this->staff->roles()->first();
        Permission::firstOrCreate(['name' => 'read-taxes'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'update-taxes'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'create-taxes'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'delete-taxes'])->assignRole($role);

        $others = $this->taxes->first();
        $tax    = factory(Tax::class)->create();
        $form   = factory(Tax::class)->make()->toArray();
        $form2  = factory(Tax::class)->make()->toArray();
        $form3  = factory(Tax::class)->make()->toArray();

        $this->actingAs($this->staff)->ajax()->get($this->route . '?limit=10&byColumn=false&orderBy=date+desc&page=1')->assertOk();
        $this->actingAs($this->staff)->ajax()->get($this->route . $tax->id)->assertOk();
        $this->actingAs($this->staff)->ajax()->post($this->route, $form)->assertOk();
        $this->actingAs($this->staff)->ajax()->put($this->route . $tax->id, $form2)->assertOk();
        $this->actingAs($this->staff)->ajax()->delete($this->route . $tax->id)->assertOk();

        // Can update and delete taxes added by other users
        $this->actingAs($this->staff)->ajax()->put($this->route . $others->id, $form3)->assertOk();
        $this->actingAs($this->staff)->ajax()->delete($this->route . $others->id)->assertOk();
    }

    public function testMPS4PublicCannotPerformAnyActionOnTax()
    {
        $this->ajax()->get($this->route)->assertUnauthorized();
        $this->ajax()->post($this->route, [])->assertUnauthorized();
        $this->ajax()->put($this->route . '1', [])->assertUnauthorized();
        $this->ajax()->delete($this->route . '1')->assertUnauthorized();
    }
}
