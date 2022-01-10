<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Hall;
use Modules\MPS\Models\Account;
use Modules\MPS\Models\Location;
use Modules\MPS\Models\HallTable;
use Modules\MPS\Models\Permission;
use Modules\MPS\Tests\MPSTestCase;

class HallTablePermissionsTest extends MPSTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->super = $this->createUser('super');
        $this->staff = $this->createUser('staff');
        $this->route = url(module('route')) . '/app/tables/';

        $account           = factory(Account::class)->create();
        $this->location    = factory(Location::class)->create(['account_id' => $account->id]);
        $this->hall        = factory(Hall::class)->create(['location_id' => $this->location->id]);
        $this->hall_tables = factory(HallTable::class, 5)->create(['hall_id' => $this->hall->id]);
    }

    public function testMPS1SuperPermissionsForHallTableRoute()
    {
        $hall_table = $this->hall_tables->first();

        // can access the view route
        $this->actingAs($this->super)->ajax()->get($this->route . $hall_table->id)->assertOk();

        // can access the store route
        $this->actingAs($this->super)->ajax()->post($this->route, [])->assertStatus(422);

        // can access the update route
        $this->actingAs($this->super)->ajax()->put($this->route . $hall_table->id, [])->assertStatus(422);

        // can access update route of others
        $this->actingAs($this->super)->ajax()->put($this->route . $hall_table->id, [])->assertStatus(422);

        // can delete
        $this->actingAs($this->super)->ajax()->delete($this->route . $hall_table->id)->assertOk();
    }

    public function testMPS2HallTableRolePermissionsForStaffWithNoPermissions()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $hall_table = factory(HallTable::class)->create(['hall_id' => $this->hall->id]);
        $form       = factory(HallTable::class)->make(['hall_id' => $this->hall->id])->toArray();

        $this->actingAs($this->staff)->ajax()->get($this->route . $hall_table->id)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->get($this->route . '?limit=10&byColumn=false&orderBy=date+desc&page=1&hall=' . $this->hall->id)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->post($this->route, $form)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->put($this->route . $hall_table->id, $form)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->delete($this->route . $hall_table->id)->assertStatus(403);
    }

    public function testMPS3HallTableRolePermissionsForStaffWithPermissions()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        // hall_table
        $role = $this->staff->roles()->first();
        Permission::firstOrCreate(['name' => 'read-tables'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'update-tables'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'create-tables'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'delete-tables'])->assignRole($role);

        $others     = $this->hall_tables->first();
        $hall_table = factory(HallTable::class)->create(['hall_id' => $this->hall->id]);
        $form       = factory(HallTable::class)->make(['hall_id' => $this->hall->id])->toArray();
        $form2      = factory(HallTable::class)->make(['hall_id' => $this->hall->id])->toArray();
        $form3      = factory(HallTable::class)->make(['hall_id' => $this->hall->id])->toArray();

        $this->actingAs($this->staff)->ajax()->get($this->route . '?limit=10&byColumn=false&orderBy=date+desc&page=1&hall=' . $this->hall->id)->assertOk();
        $this->actingAs($this->staff)->ajax()->get($this->route . $hall_table->id)->assertOk();
        $this->actingAs($this->staff)->ajax()->post($this->route, $form)->assertOk();
        $this->actingAs($this->staff)->ajax()->put($this->route . $hall_table->id, $form2)->assertOk();
        $this->actingAs($this->staff)->ajax()->delete($this->route . $hall_table->id)->assertOk();

        // Can update and delete hall_tables added by other users
        $this->actingAs($this->staff)->ajax()->put($this->route . $others->id, $form3)->assertOk();
        $this->actingAs($this->staff)->ajax()->delete($this->route . $others->id)->assertOk();
    }

    public function testMPS4PublicCannotPerformAnyActionOnHallTable()
    {
        $this->ajax()->get($this->route)->assertUnauthorized();
        $this->ajax()->post($this->route, [])->assertUnauthorized();
        $this->ajax()->put($this->route . '1', [])->assertUnauthorized();
        $this->ajax()->delete($this->route . '1')->assertUnauthorized();
    }
}
