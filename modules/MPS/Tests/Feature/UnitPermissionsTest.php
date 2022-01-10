<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Unit;
use Modules\MPS\Models\Permission;
use Modules\MPS\Tests\MPSTestCase;

class UnitPermissionsTest extends MPSTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->super = $this->createUser('super');
        $this->staff = $this->createUser('staff');
        $this->route = url(module('route')) . '/app/units/';

        $this->units = factory(Unit::class, 5)->create();
    }

    public function testMPS1SuperPermissionsForUnitRoute()
    {
        $unit = $this->units->first();

        // can access the view route
        $this->actingAs($this->super)->ajax()->get($this->route . $unit->id)->assertOk();

        // can access the store route
        $this->actingAs($this->super)->ajax()->post($this->route, [])->assertStatus(422);

        // can access the update route
        $this->actingAs($this->super)->ajax()->put($this->route . $unit->id, [])->assertStatus(422);

        // can access update route of others
        $this->actingAs($this->super)->ajax()->put($this->route . $unit->id, [])->assertStatus(422);

        // can delete
        $this->actingAs($this->super)->ajax()->delete($this->route . $unit->id)->assertOk();
    }

    public function testMPS2UnitRolePermissionsForStaffWithNoPermissions()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $unit = factory(Unit::class)->create();
        $form = factory(Unit::class)->make()->toArray();

        $this->actingAs($this->staff)->ajax()->get($this->route . $unit->id)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->get($this->route . '?limit=10&byColumn=false&orderBy=date+desc&page=1')->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->post($this->route, $form)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->put($this->route . $unit->id, $form)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->delete($this->route . $unit->id)->assertStatus(403);
    }

    public function testMPS3UnitRolePermissionsForStaffWithPermissions()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        // unit
        $role = $this->staff->roles()->first();
        Permission::firstOrCreate(['name' => 'read-units'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'update-units'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'create-units'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'delete-units'])->assignRole($role);

        $others = $this->units->first();
        $unit   = factory(Unit::class)->create();
        $form   = factory(Unit::class)->make()->toArray();
        $form2  = factory(Unit::class)->make()->toArray();
        $form3  = factory(Unit::class)->make()->toArray();

        $this->actingAs($this->staff)->ajax()->get($this->route . '?limit=10&byColumn=false&orderBy=date+desc&page=1')->assertOk();
        $this->actingAs($this->staff)->ajax()->get($this->route . $unit->id)->assertOk();
        $this->actingAs($this->staff)->ajax()->post($this->route, $form)->assertOk();
        $this->actingAs($this->staff)->ajax()->put($this->route . $unit->id, $form2)->assertOk();
        $this->actingAs($this->staff)->ajax()->delete($this->route . $unit->id)->assertOk();

        // Can update and delete units added by other users
        $this->actingAs($this->staff)->ajax()->put($this->route . $others->id, $form3)->assertOk();
        $this->actingAs($this->staff)->ajax()->delete($this->route . $others->id)->assertOk();
    }

    public function testMPS4PublicCannotPerformAnyActionOnUnit()
    {
        $this->ajax()->get($this->route)->assertUnauthorized();
        $this->ajax()->post($this->route, [])->assertUnauthorized();
        $this->ajax()->put($this->route . '1', [])->assertUnauthorized();
        $this->ajax()->delete($this->route . '1')->assertUnauthorized();
    }
}
