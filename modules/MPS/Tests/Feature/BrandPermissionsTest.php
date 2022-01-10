<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Brand;
use Modules\MPS\Models\Permission;
use Modules\MPS\Tests\MPSTestCase;

class BrandPermissionsTest extends MPSTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->super = $this->createUser('super');
        $this->admin = $this->createUser('admin');
        $this->staff = $this->createUser('staff');
        $this->route = url(module('route')) . '/app/brands/';

        $this->brands = factory(Brand::class, 5)->create();
    }

    public function testMPS1SuperPermissionsForBrandRoute()
    {
        $brand = $this->brands->first();

        // can access the view route
        $this->actingAs($this->super)->ajax()->get($this->route . $brand->id)->assertOk();

        // can access the store route
        $this->actingAs($this->super)->ajax()->post($this->route, [])->assertStatus(422);

        // can access the update route
        $this->actingAs($this->super)->ajax()->put($this->route . $brand->id, [])->assertStatus(422);

        // can access update route of others
        $this->actingAs($this->super)->ajax()->put($this->route . $brand->id, [])->assertStatus(422);

        // can delete
        $this->actingAs($this->super)->ajax()->delete($this->route . $brand->id)->assertOk();
    }

    public function testMPS2BrandRolePermissionsForStaffWithNoPermissions()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $brand = factory(Brand::class)->create();
        $form  = factory(Brand::class)->make()->toArray();

        $this->actingAs($this->staff)->ajax()->get($this->route . $brand->id)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->get($this->route . '?limit=10&byColumn=false&orderBy=date+desc&page=1')->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->post($this->route, $form)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->put($this->route . $brand->id, $form)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->delete($this->route . $brand->id)->assertStatus(403);
    }

    public function testMPS3BrandRolePermissionsForStaffWithPermissions()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        // brand
        $role = $this->staff->roles()->first();
        Permission::firstOrCreate(['name' => 'read-brands'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'update-brands'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'create-brands'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'delete-brands'])->assignRole($role);

        $others = $this->brands->first();
        $brand  = factory(Brand::class)->create();
        $form   = factory(Brand::class)->make()->toArray();
        $form2  = factory(Brand::class)->make()->toArray();
        $form3  = factory(Brand::class)->make()->toArray();

        $this->actingAs($this->staff)->ajax()->get($this->route . '?limit=10&byColumn=false&orderBy=date+desc&page=1')->assertOk();
        $this->actingAs($this->staff)->ajax()->get($this->route . $brand->id)->assertOk();
        $this->actingAs($this->staff)->ajax()->post($this->route, $form)->assertOk();
        $this->actingAs($this->staff)->ajax()->put($this->route . $brand->id, $form2)->assertOk();
        $this->actingAs($this->staff)->ajax()->delete($this->route . $brand->id)->assertOk();

        // Can update and delete brands added by other users
        $this->actingAs($this->staff)->ajax()->put($this->route . $others->id, $form3)->assertOk();
        $this->actingAs($this->staff)->ajax()->delete($this->route . $others->id)->assertOk();
    }

    public function testMPS4PublicCannotPerformAnyActionOnBrand()
    {
        $this->ajax()->get($this->route)->assertUnauthorized();
        $this->ajax()->post($this->route, [])->assertUnauthorized();
        $this->ajax()->put($this->route . '1', [])->assertUnauthorized();
        $this->ajax()->delete($this->route . '1')->assertUnauthorized();
    }
}
