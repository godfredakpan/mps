<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Category;
use Modules\MPS\Models\Permission;
use Modules\MPS\Tests\MPSTestCase;

class CategoryPermissionsTest extends MPSTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->super = $this->createUser('super');
        $this->admin = $this->createUser('admin');
        $this->staff = $this->createUser('staff');
        $this->route = url(module('route')) . '/app/categories/';

        $this->categories = factory(Category::class, 5)->create();
    }

    public function testMPS1SuperPermissionsForCategoryRoute()
    {
        $category = $this->categories->first();

        // can access the view route
        $this->actingAs($this->super)->ajax()->get($this->route . $category->id)->assertOk();

        // can access the store route
        $this->actingAs($this->super)->ajax()->post($this->route, [])->assertStatus(422);

        // can access the update route
        $this->actingAs($this->super)->ajax()->put($this->route . $category->id, [])->assertStatus(422);

        // can access update route of others
        $this->actingAs($this->super)->ajax()->put($this->route . $category->id, [])->assertStatus(422);

        // can delete
        $this->actingAs($this->super)->ajax()->delete($this->route . $category->id)->assertOk();
    }

    public function testMPS2CategoryRolePermissionsForStaffWithNoPermissions()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $category = factory(Category::class)->create();
        $form     = factory(Category::class)->make()->toArray();

        $this->actingAs($this->staff)->ajax()->get($this->route . $category->id)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->get($this->route . '?limit=10&byColumn=false&orderBy=date+desc&page=1')->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->post($this->route, $form)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->put($this->route . $category->id, $form)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->delete($this->route . $category->id)->assertStatus(403);
    }

    public function testMPS3CategoryRolePermissionsForStaffWithPermissions()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        // category
        $role = $this->staff->roles()->first();
        Permission::firstOrCreate(['name' => 'read-categories'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'update-categories'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'create-categories'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'delete-categories'])->assignRole($role);

        $others   = $this->categories->first();
        $category = factory(Category::class)->create();
        $form     = factory(Category::class)->make()->toArray();
        $form2    = factory(Category::class)->make()->toArray();
        $form3    = factory(Category::class)->make()->toArray();

        $this->actingAs($this->staff)->ajax()->get($this->route . '?limit=10&byColumn=false&orderBy=date+desc&page=1')->assertOk();
        $this->actingAs($this->staff)->ajax()->get($this->route . $category->id)->assertOk();
        $this->actingAs($this->staff)->ajax()->post($this->route, $form)->assertOk();
        $this->actingAs($this->staff)->ajax()->put($this->route . $category->id, $form2)->assertOk();
        $this->actingAs($this->staff)->ajax()->delete($this->route . $category->id)->assertOk();

        // Can update and delete categories added by other users
        $this->actingAs($this->staff)->ajax()->put($this->route . $others->id, $form3)->assertOk();
        $this->actingAs($this->staff)->ajax()->delete($this->route . $others->id)->assertOk();
    }

    public function testMPS4PublicCannotPerformAnyActionOnCategory()
    {
        $this->ajax()->get($this->route)->assertUnauthorized();
        $this->ajax()->post($this->route, [])->assertUnauthorized();
        $this->ajax()->put($this->route . '1', [])->assertUnauthorized();
        $this->ajax()->delete($this->route . '1')->assertUnauthorized();
    }
}
