<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Hall;
use Modules\MPS\Models\Account;
use Modules\MPS\Models\Location;
use Modules\MPS\Models\Permission;
use Modules\MPS\Tests\MPSTestCase;

class HallPermissionsTest extends MPSTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->super = $this->createUser('super');
        $this->staff = $this->createUser('staff');
        $this->route = url(module('route')) . '/app/halls/';

        $account        = factory(Account::class)->create();
        $this->location = factory(Location::class)->create(['account_id' => $account->id]);
        $this->halls    = factory(Hall::class, 5)->create(['location_id' => $this->location->id]);
    }

    public function testMPS1SuperPermissionsForHallRoute()
    {
        $hall = $this->halls->first();

        // can access the view route
        $this->actingAs($this->super)->ajax()->get($this->route . $hall->id)->assertOk();

        // can access the store route
        $this->actingAs($this->super)->ajax()->post($this->route, [])->assertStatus(422);

        // can access the update route
        $this->actingAs($this->super)->ajax()->put($this->route . $hall->id, [])->assertStatus(422);

        // can access update route of others
        $this->actingAs($this->super)->ajax()->put($this->route . $hall->id, [])->assertStatus(422);

        // can delete
        $this->actingAs($this->super)->ajax()->delete($this->route . $hall->id)->assertOk();
    }

    public function testMPS2HallRolePermissionsForStaffWithNoPermissions()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $hall = factory(Hall::class)->create(['location_id' => $this->location->id]);
        $form = factory(Hall::class)->make(['location_id' => $this->location->id])->toArray();

        $this->actingAs($this->staff)->ajax()->get($this->route . $hall->id)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->get($this->route . '?limit=10&byColumn=false&orderBy=date+desc&page=1')->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->post($this->route, $form)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->put($this->route . $hall->id, $form)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->delete($this->route . $hall->id)->assertStatus(403);
    }

    public function testMPS3HallRolePermissionsForStaffWithPermissions()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        // hall
        $role = $this->staff->roles()->first();
        Permission::firstOrCreate(['name' => 'read-halls'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'update-halls'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'create-halls'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'delete-halls'])->assignRole($role);

        $others = $this->halls->first();
        $hall   = factory(Hall::class)->create(['location_id' => $this->location->id]);
        $form   = factory(Hall::class)->make(['location_id' => $this->location->id])->toArray();
        $form2  = factory(Hall::class)->make(['location_id' => $this->location->id])->toArray();
        $form3  = factory(Hall::class)->make(['location_id' => $this->location->id])->toArray();

        $this->actingAs($this->staff)->ajax()->get($this->route . '?limit=10&byColumn=false&orderBy=date+desc&page=1')->assertOk();
        $this->actingAs($this->staff)->ajax()->get($this->route . $hall->id)->assertOk();
        $this->actingAs($this->staff)->ajax()->post($this->route, $form)->assertOk();
        $this->actingAs($this->staff)->ajax()->put($this->route . $hall->id, $form2)->assertOk();
        $this->actingAs($this->staff)->ajax()->delete($this->route . $hall->id)->assertOk();

        // Can update and delete halls added by other users
        $this->actingAs($this->staff)->ajax()->put($this->route . $others->id, $form3)->assertOk();
        $this->actingAs($this->staff)->ajax()->delete($this->route . $others->id)->assertOk();
    }

    public function testMPS4PublicCannotPerformAnyActionOnHall()
    {
        $this->ajax()->get($this->route)->assertUnauthorized();
        $this->ajax()->post($this->route, [])->assertUnauthorized();
        $this->ajax()->put($this->route . '1', [])->assertUnauthorized();
        $this->ajax()->delete($this->route . '1')->assertUnauthorized();
    }
}
