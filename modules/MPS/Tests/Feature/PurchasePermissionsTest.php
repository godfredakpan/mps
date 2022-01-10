<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Item;
use Modules\MPS\Models\Unit;
use Modules\MPS\Models\Account;
use Modules\MPS\Models\Category;
use Modules\MPS\Models\Location;
use Modules\MPS\Models\Permission;
use Modules\MPS\Tests\MPSTestCase;

class PurchasePermissionsTest extends MPSTestCase
{
    use TestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->super    = $this->createUser('super');
        $this->staff    = $this->createUser('staff');
        $this->unit     = factory(Unit::class)->create();
        $this->account  = factory(Account::class)->create();
        $this->category = factory(Category::class)->create();
        $this->route    = url(module('route')) . '/app/purchases/';
        $this->location = factory(Location::class)->create(['account_id' => $this->account->id]);
        session(['location_id' => $this->location->id]);
        $this->item = factory(Item::class)->create(['unit_id' => $this->unit->id]);
        $this->item->categories()->sync($this->category->id);
        $this->item->stock()->create(['quantity' => 20]);

        $this->purchases = $this->createPurchase($this, 2, $this->super);
    }

    public function testMPS1SuperPermissionsForPurchaseRoute()
    {
        $purchase = $this->purchases->first();

        // can access the view route
        $this->actingAs($this->super)->ajax()->get($this->route . $purchase->id)->assertOk();

        // can access the store route
        $this->actingAs($this->super)->ajax()->post($this->route, [])->assertStatus(422);

        // can access the update route
        $this->actingAs($this->super)->ajax()->put($this->route . $purchase->id, [])->assertStatus(422);

        // can access update route of others
        $this->actingAs($this->super)->ajax()->put($this->route . $purchase->id, [])->assertStatus(422);

        // can delete
        $this->actingAs($this->super)->ajax()->delete($this->route . $purchase->id)->assertOk();
    }

    public function testMPS2PurchaseRolePermissionsForStaffWithNoPermissions()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $purchase = $this->createPurchase($this, 1, $this->staff);
        $form     = $this->createPurchaseForm($this, $this->staff, 1, null, $this->unit);

        $this->actingAs($this->staff)->ajax()->get($this->route . $purchase->id)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->get($this->route . '?limit=10&byColumn=false&orderBy=date+desc&page=1')->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->post($this->route, $form)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->put($this->route . $purchase->id, $form)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->delete($this->route . $purchase->id)->assertStatus(403);
    }

    public function testMPS3PurchaseRolePermissionsForStaffWithPermissions()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        // purchase
        $role = $this->staff->roles()->first();
        Permission::firstOrCreate(['name' => 'read-purchases'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'create-purchases'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'update-purchases'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'delete-purchases'])->assignRole($role);

        $purchase = $this->createPurchase($this, 1, $this->staff);
        $others   = $this->createPurchase($this, 1, $this->super);
        $form     = $this->createPurchaseForm($this, $this->staff, 1, null, $this->unit);

        $this->actingAs($this->staff)->ajax()->get($this->route . '?limit=10&byColumn=false&orderBy=date+desc&page=1')->assertOk();
        $this->actingAs($this->staff)->ajax()->get($this->route . $purchase->id)->assertOk();
        $this->actingAs($this->staff)->ajax()->post($this->route, $form)->assertOk();
        $this->actingAs($this->staff)->ajax()->put($this->route . $purchase->id, $form)->assertOk();
        $this->actingAs($this->staff)->ajax()->delete($this->route . $purchase->id)->assertOk();

        // Can't update or delete other users record
        $this->actingAs($this->staff)->ajax()->put($this->route . $others->id, $form)->assertStatus(404);
        $this->actingAs($this->staff)->ajax()->delete($this->route . $others->id)->assertStatus(404);
    }

    public function testMPS4PublicCannotPerformAnyActionOnPurchase()
    {
        $this->ajax()->get($this->route)->assertUnauthorized();
        $this->ajax()->post($this->route, [])->assertUnauthorized();
        $this->ajax()->put($this->route . '1', [])->assertUnauthorized();
        $this->ajax()->delete($this->route . '1')->assertUnauthorized();
    }
}
