<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Field;
use Modules\MPS\Models\Permission;
use Modules\MPS\Tests\MPSTestCase;

class CustomFieldPermissionsTest extends MPSTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->super = $this->createUser('super');
        $this->admin = $this->createUser('admin');
        $this->staff = $this->createUser('staff');
        $this->route = url(module('route')) . '/app/fields/';

        $this->fields = factory(Field::class, 5)->create();
    }

    public function testMPS1SuperPermissionsForFieldRoute()
    {
        $field = $this->fields->first();

        // can access the view route
        $this->actingAs($this->super)->ajax()->get($this->route . $field->id)->assertOk();

        // can access the store route
        $this->actingAs($this->super)->ajax()->post($this->route, [])->assertStatus(422);

        // can access the update route
        $this->actingAs($this->super)->ajax()->put($this->route . $field->id, [])->assertStatus(422);

        // can access update route of others
        $this->actingAs($this->super)->ajax()->put($this->route . $field->id, [])->assertStatus(422);

        // can delete
        $this->actingAs($this->super)->ajax()->delete($this->route . $field->id)->assertOk();
    }

    public function testMPS2FieldRolePermissionsForStaffWithNoPermissions()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $field  = $this->fields->first();
        $others = factory(Field::class)->create();
        $form   = factory(Field::class)->make()->toArray();

        $this->actingAs($this->staff)->ajax()->get($this->route . $field->id)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->get($this->route . '?limit=10&byColumn=false&orderBy=date+desc&page=1')->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->post($this->route, $form)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->put($this->route . $field->id, $form)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->delete($this->route . $field->id)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->delete($this->route . $others->id)->assertStatus(403);
    }

    public function testMPS3FieldRolePermissionsForStaffWithPermissions()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        // field
        $role = $this->staff->roles()->first();
        Permission::firstOrCreate(['name' => 'read-fields'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'update-fields'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'create-fields'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'delete-fields'])->assignRole($role);

        $field  = $this->fields->first();
        $others = factory(Field::class)->create();
        $form   = factory(Field::class)->make()->toArray();
        $form2  = factory(Field::class)->make()->toArray();
        $form3  = factory(Field::class)->make()->toArray();

        $this->actingAs($this->staff)->ajax()->get($this->route . '?limit=10&byColumn=false&orderBy=date+desc&page=1')->assertOk();
        $this->actingAs($this->staff)->ajax()->get($this->route . $field->id)->assertOk();
        $this->actingAs($this->staff)->ajax()->post($this->route, $form)->assertOk();
        $this->actingAs($this->staff)->ajax()->put($this->route . $field->id, $form2)->assertOk();
        $this->actingAs($this->staff)->ajax()->put($this->route . $others->id, $form3)->assertOk();
        $this->actingAs($this->staff)->ajax()->delete($this->route . $field->id)->assertOk();
        $this->actingAs($this->staff)->ajax()->delete($this->route . $others->id)->assertOk();
    }

    public function testMPS4PublicCannotPerformAnyActionOnField()
    {
        $this->ajax()->get($this->route)->assertUnauthorized();
        $this->ajax()->post($this->route, [])->assertUnauthorized();
        $this->ajax()->put($this->route . '1', [])->assertUnauthorized();
        $this->ajax()->delete($this->route . '1')->assertUnauthorized();
    }
}
