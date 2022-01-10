<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Item;
use Modules\MPS\Models\Category;
use Modules\MPS\Models\Permission;
use Modules\MPS\Tests\MPSTestCase;

class ItemPermissionsTest extends MPSTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->super = $this->createUser('super');
        $this->admin = $this->createUser('admin');
        $this->staff = $this->createUser('staff');
        $this->route = url(module('route')) . '/app/items/';

        $this->category = factory(Category::class)->create();
        $this->items    = factory(Item::class, 5)->create();
    }

    public function testMPS1SuperPermissionsForItemRoute()
    {
        $item = $this->items->first();

        // can access the view route
        $this->actingAs($this->super)->ajax()->get($this->route . $item->id)->assertOk();

        // can access the store route
        $this->actingAs($this->super)->ajax()->post($this->route, [])->assertStatus(422);

        // can access the update route
        $this->actingAs($this->super)->ajax()->put($this->route . $item->id, [])->assertStatus(422);

        // can access update route of others
        $this->actingAs($this->super)->ajax()->put($this->route . $item->id, [])->assertStatus(422);

        // can delete
        $this->actingAs($this->super)->ajax()->delete($this->route . $item->id)->assertOk();
    }

    public function testMPS2ItemRolePermissionsForStaffWithNoPermissions()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $item = factory(Item::class)->create();
        $form = factory(Item::class)->make(['category_id' => $this->category->id])->toArray();

        $this->actingAs($this->staff)->ajax()->get($this->route . $item->id)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->get($this->route . '?limit=10&byColumn=false&orderBy=date+desc&page=1')->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->post($this->route, $form)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->put($this->route . $item->id, $form)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->delete($this->route . $item->id)->assertStatus(403);
    }

    public function testMPS3ItemRolePermissionsForStaffWithPermissions()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        // item
        $role = $this->staff->roles()->first();
        Permission::firstOrCreate(['name' => 'read-items'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'update-items'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'create-items'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'delete-items'])->assignRole($role);

        $item  = factory(Item::class)->create();
        $form  = factory(Item::class)->make(['category_id' => $this->category->id])->toArray();
        $form2 = factory(Item::class)->make(['category_id' => $this->category->id])->toArray();

        $this->actingAs($this->staff)->ajax()->get($this->route . '?limit=10&byColumn=false&orderBy=date+desc&page=1')->assertOk();
        $this->actingAs($this->staff)->ajax()->get($this->route . $item->id)->assertOk();
        $this->actingAs($this->staff)->ajax()->post($this->route, $form)->assertOk();
        $this->actingAs($this->staff)->ajax()->put($this->route . $item->id, $form2)->assertOk();
        $this->actingAs($this->staff)->ajax()->delete($this->route . $item->id)->assertOk();
    }

    public function testMPS4PublicCannotPerformAnyActionOnItem()
    {
        $this->ajax()->get($this->route)->assertUnauthorized();
        $this->ajax()->post($this->route, [])->assertUnauthorized();
        $this->ajax()->put($this->route . '1', [])->assertUnauthorized();
        $this->ajax()->delete($this->route . '1')->assertUnauthorized();
    }
}
