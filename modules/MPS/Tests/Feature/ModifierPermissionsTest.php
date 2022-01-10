<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Item;
use Modules\MPS\Models\Modifier;
use Modules\MPS\Models\Permission;
use Modules\MPS\Tests\MPSTestCase;
use Modules\MPS\Models\ModifierOption;

class ModifierPermissionsTest extends MPSTestCase
{
    use TestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->super = $this->createUser('super');
        $this->admin = $this->createUser('admin');
        $this->staff = $this->createUser('staff');
        $this->route = url(module('route')) . '/app/modifiers/';

        $this->modifiers = factory(Modifier::class, 5)->create();
        $this->item      = factory(Item::class)->create(['code' => 1]);
        $this->item2     = factory(Item::class)->create(['code' => 2]);
        $this->item3     = factory(Item::class)->create(['code' => 3]);
        // foreach ($this->locations as $location) {
        //     session(['location_id' => $location->id]);
        //     $this->item->stock()->create(['quantity' => 50]);
        // }
    }

    public function testMPS1SuperPermissionsForModifierRoute()
    {
        $modifier = $this->modifiers->first();

        // can access the view route
        $this->actingAs($this->super)->ajax()->get($this->route . $modifier->id)->assertOk();

        // can access the store route
        $this->actingAs($this->super)->ajax()->post($this->route, [])->assertStatus(422);

        // can access the update route
        $this->actingAs($this->super)->ajax()->put($this->route . $modifier->id, [])->assertStatus(422);

        // can access update route of others
        $this->actingAs($this->super)->ajax()->put($this->route . $modifier->id, [])->assertStatus(422);

        // can delete
        $this->actingAs($this->super)->ajax()->delete($this->route . $modifier->id)->assertOk();
    }

    public function testMPS2ModifierRolePermissionsForStaffWithNoPermissions()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $modifier          = $this->createModifier($this, 1, $this->staff);
        $form              = factory(Modifier::class)->make()->toArray();
        $form['options'][] = factory(ModifierOption::class)->make(['item_id' => $this->item2->id])->toArray();

        $this->actingAs($this->staff)->ajax()->get($this->route . $modifier->id)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->get($this->route . '?limit=10&byColumn=false&orderBy=date+desc&page=1')->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->post($this->route, $form)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->put($this->route . $modifier->id, $form)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->delete($this->route . $modifier->id)->assertStatus(403);
    }

    public function testMPS3ModifierRolePermissionsForStaffWithPermissions()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        // modifier
        $role = $this->staff->roles()->first();
        Permission::firstOrCreate(['name' => 'read-modifiers'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'update-modifiers'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'create-modifiers'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'delete-modifiers'])->assignRole($role);

        $others   = $this->modifiers->first();
        $modifier = $this->createModifier($this, 1, $this->staff);
        // $modifier              = factory(Modifier::class)->create();
        $form               = factory(Modifier::class)->make()->toArray();
        $form['options'][]  = factory(ModifierOption::class)->make(['item_id' => $this->item->id])->toArray();
        $form2              = factory(Modifier::class)->make()->toArray();
        $form2['options'][] = factory(ModifierOption::class)->make(['item_id' => $this->item2->id])->toArray();
        $form3              = factory(Modifier::class)->make()->toArray();
        $form3['options'][] = factory(ModifierOption::class)->make(['item_id' => $this->item3->id])->toArray();

        $this->actingAs($this->staff)->ajax()->get($this->route . '?limit=10&byColumn=false&orderBy=date+desc&page=1')->assertOk();
        $this->actingAs($this->staff)->ajax()->get($this->route . $modifier->id)->assertOk();
        $this->actingAs($this->staff)->ajax()->post($this->route, $form)->assertOk();
        $this->actingAs($this->staff)->ajax()->put($this->route . $modifier->id, $form2)->assertOk();
        $this->actingAs($this->staff)->ajax()->delete($this->route . $modifier->id)->assertOk();

        // Can update and delete modifiers added by other users
        $this->actingAs($this->staff)->ajax()->put($this->route . $others->id, $form3)->assertOk();
        $this->actingAs($this->staff)->ajax()->delete($this->route . $others->id)->assertOk();
    }

    public function testMPS4PublicCannotPerformAnyActionOnModifier()
    {
        $this->ajax()->get($this->route)->assertUnauthorized();
        $this->ajax()->post($this->route, [])->assertUnauthorized();
        $this->ajax()->put($this->route . '1', [])->assertUnauthorized();
        $this->ajax()->delete($this->route . '1')->assertUnauthorized();
    }
}
