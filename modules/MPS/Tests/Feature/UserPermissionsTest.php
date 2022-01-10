<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Tests\MPSTestCase;

class UserPermissionsTest extends MPSTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->super = $this->createUser('super');
        $this->admin = $this->createUser('admin');
        $this->staff = $this->createUser('staff');
        $this->route = url(module('route')) . '/app/users/';
        $this->users = factory(\App\User::class, 5)->create();
    }

    public function testMPS1SuperPermissionsForUserRoute()
    {
        $user = $this->users->first();

        // can access the view route
        $this->actingAs($this->super)->ajax()->get($this->route . $user->id)->assertOk();

        // can access the store route
        $this->actingAs($this->super)->ajax()->post($this->route, [])->assertStatus(422);

        // can access the update route
        $this->actingAs($this->super)->ajax()->put($this->route . $user->id, [])->assertStatus(422);

        // can access update route of others
        $this->actingAs($this->super)->ajax()->put($this->route . $user->id, [])->assertStatus(422);

        // can delete
        $this->actingAs($this->super)->ajax()->delete($this->route . $user->id)->assertOk();
    }

    public function testMPS2AdminPermissionsForUserRoute()
    {
        $user = $this->users->first();

        // cannot access the view route
        $this->actingAs($this->admin)->ajax()->get($this->route . $user->id)->assertStatus(403);

        // cannot access the store route
        $this->actingAs($this->admin)->ajax()->post($this->route, [])->assertStatus(403);

        // cannot access the update route
        $this->actingAs($this->admin)->ajax()->put($this->route . $user->id, [])->assertStatus(403);

        // cannot delete
        $this->actingAs($this->admin)->ajax()->delete($this->route . $user->id)->assertStatus(403);
    }

    public function testMPS3StaffPermissionsForUserRoute()
    {
        $user = $this->users->first();

        // cannot access the view route
        $this->actingAs($this->staff)->ajax()->get($this->route . $user->id)->assertStatus(403);

        // cannot access the store route
        $this->actingAs($this->staff)->ajax()->post($this->route, [])->assertStatus(403);

        // cannot access the update route
        $this->actingAs($this->staff)->ajax()->put($this->route . $user->id, [])->assertStatus(403);

        // cannot delete
        $this->actingAs($this->staff)->ajax()->delete($this->route . $user->id)->assertStatus(403);
    }

    public function testMPS4PublicCannotPerformAnyActionOnUser()
    {
        $this->ajax()->get($this->route)->assertUnauthorized();
        $this->ajax()->post($this->route, [])->assertUnauthorized();
        $this->ajax()->put($this->route . '1', [])->assertUnauthorized();
        $this->ajax()->delete($this->route . '1')->assertUnauthorized();
    }
}
