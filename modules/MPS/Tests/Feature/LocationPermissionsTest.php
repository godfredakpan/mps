<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Account;
use Modules\MPS\Models\Location;
use Modules\MPS\Models\Register;
use Modules\MPS\Tests\MPSTestCase;

class LocationPermissionsTest extends MPSTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->super = $this->createUser('super');
        $this->admin = $this->createUser('admin');
        $this->staff = $this->createUser('staff');
        $this->route = url(module('route')) . '/app/locations/';

        $account         = factory(Account::class)->create();
        $registers       = factory(Register::class, mt_rand(1, 3))->make()->toArray();
        $this->locations = factory(Location::class, 5)->create(['account_id' => $account->id])->each(function ($location) {
            $location->registers()->saveMany(factory('Modules\MPS\Models\Register', mt_rand(2, 3))->make());
        });
    }

    public function testMPS1SuperPermissionsForLocationRoute()
    {
        $location = $this->locations->first();

        // can access the view route
        $this->actingAs($this->super)->ajax()->get($this->route . $location->id)->assertOk();

        // can access the store route
        $this->actingAs($this->super)->ajax()->post($this->route, [])->assertStatus(422);

        // can access the update route
        $this->actingAs($this->super)->ajax()->put($this->route . $location->id, [])->assertStatus(422);

        // can access update route of others
        $this->actingAs($this->super)->ajax()->put($this->route . $location->id, [])->assertStatus(422);

        // can delete
        $this->actingAs($this->super)->ajax()->delete($this->route . $location->id)->assertOk();
    }

    public function testMPS2AdminPermissionsForLocationRoute()
    {
        $location = $this->locations->first();

        // cannot access the view route
        $this->actingAs($this->admin)->ajax()->get($this->route . $location->id)->assertStatus(403);

        // cannot access the store route
        $this->actingAs($this->admin)->ajax()->post($this->route, [])->assertStatus(403);

        // cannot access the update route
        $this->actingAs($this->admin)->ajax()->put($this->route . $location->id, [])->assertStatus(403);

        // cannot delete
        $this->actingAs($this->admin)->ajax()->delete($this->route . $location->id)->assertStatus(403);
    }

    public function testMPS3StaffPermissionsForLocationRoute()
    {
        $location = $this->locations->first();

        // cannot access the view route
        $this->actingAs($this->staff)->ajax()->get($this->route . $location->id)->assertStatus(403);

        // cannot access the store route
        $this->actingAs($this->staff)->ajax()->post($this->route, [])->assertStatus(403);

        // cannot access the update route
        $this->actingAs($this->staff)->ajax()->put($this->route . $location->id, [])->assertStatus(403);

        // cannot delete
        $this->actingAs($this->staff)->ajax()->delete($this->route . $location->id)->assertStatus(403);
    }

    public function testMPS4PublicCannotPerformAnyActionOnLocation()
    {
        $this->ajax()->get($this->route)->assertUnauthorized();
        $this->ajax()->post($this->route, [])->assertUnauthorized();
        $this->ajax()->put($this->route . '1', [])->assertUnauthorized();
        $this->ajax()->delete($this->route . '1')->assertUnauthorized();
    }
}
