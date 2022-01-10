<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Item;
use Modules\MPS\Models\Account;
use Modules\MPS\Models\Location;
use Modules\MPS\Models\Permission;
use Modules\MPS\Tests\MPSTestCase;

class StockAdjustmentPermissionsTest extends MPSTestCase
{
    use TestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->super = $this->createUser('super');
        $this->staff = $this->createUser('staff');
        $this->route = url(module('route')) . '/app/adjustments/';

        $this->account  = factory(Account::class)->create();
        $this->location = factory(Location::class)->create(['account_id' => $this->account->id]);
        $this->item     = factory(Item::class)->create();

        session(['location_id' => $this->location->id]);
        $this->item->stock()->create(['quantity' => 50, 'location_id' => $this->location->id]);
    }

    public function testMPS1SuperPermissionsForStockAdjustmentRoute()
    {
        $stock_adjustment = $this->createStockAdjustment($this, 1, $this->super);

        // can access the view route
        $this->actingAs($this->super)->ajax()->get($this->route . $stock_adjustment->id)->assertOk();

        // can access the store route
        $this->actingAs($this->super)->ajax()->post($this->route, [])->assertStatus(422);

        // can access the update route
        $this->actingAs($this->super)->ajax()->put($this->route . $stock_adjustment->id, [])->assertStatus(422);

        // can access update route of others
        $this->actingAs($this->super)->ajax()->put($this->route . $stock_adjustment->id, [])->assertStatus(422);

        // can delete
        $this->actingAs($this->super)->ajax()->delete($this->route . $stock_adjustment->id)->assertOk();
    }

    public function testMPS2StockAdjustmentRolePermissionsForStaffWithNoPermissions()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $stock_adjustment = $this->createStockAdjustment($this, 1, $this->staff);
        $form             = $this->createStockAdjustmentForm($this, $this->staff, 'addition');

        $this->actingAs($this->staff)->ajax()->get($this->route . $stock_adjustment->id)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->get($this->route . '?limit=10&byColumn=false&orderBy=date+desc&page=1')->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->post($this->route, $form)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->put($this->route . $stock_adjustment->id, $form)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->delete($this->route . $stock_adjustment->id)->assertStatus(403);
    }

    public function testMPS3StockAdjustmentRolePermissionsForStaffWithPermissions()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        // stock_adjustment
        $role = $this->staff->roles()->first();
        Permission::firstOrCreate(['name' => 'read-stock-adjustments'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'create-stock-adjustments'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'update-stock-adjustments'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'delete-stock-adjustments'])->assignRole($role);

        $stock_adjustment = $this->createStockAdjustment($this, 1, $this->staff);
        $others           = $this->createStockAdjustment($this, 1, $this->super);
        $form             = $this->createStockAdjustmentForm($this, $this->staff, 'addition');

        $this->actingAs($this->staff)->ajax()->get($this->route . '?limit=10&byColumn=false&orderBy=date+desc&page=1')->assertOk();
        $this->actingAs($this->staff)->ajax()->get($this->route . $stock_adjustment->id)->assertOk();
        $this->actingAs($this->staff)->ajax()->post($this->route, $form)->assertOk();
        $this->actingAs($this->staff)->ajax()->put($this->route . $stock_adjustment->id, $form)->assertOk();
        $this->actingAs($this->staff)->ajax()->delete($this->route . $stock_adjustment->id)->assertOk();

        // Can't update or delete other users record
        $this->actingAs($this->staff)->ajax()->put($this->route . $others->id, $form)->assertStatus(404);
        $this->actingAs($this->staff)->ajax()->delete($this->route . $others->id)->assertStatus(404);
    }

    public function testMPS4PublicCannotPerformAnyActionOnStockAdjustment()
    {
        $this->ajax()->get($this->route)->assertUnauthorized();
        $this->ajax()->post($this->route, [])->assertUnauthorized();
        $this->ajax()->put($this->route . '1', [])->assertUnauthorized();
        $this->ajax()->delete($this->route . '1')->assertUnauthorized();
    }
}
