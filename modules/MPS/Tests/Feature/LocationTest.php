<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Account;
use Modules\MPS\Models\Location;
use Modules\MPS\Models\Register;
use Modules\MPS\Tests\MPSTestCase;

class LocationTest extends MPSTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->user    = $this->createUser('super');
        $this->account = factory(Account::class)->create();
        $this->route   = url(module('route')) . '/app/locations/';
    }

    public function testMPSCanCreatAndUpdateLocation()
    {
        // insert
        $rno       = mt_rand(2, 3);
        $registers = factory(Register::class, $rno)->make()->toArray();
        $location  = factory(Location::class)->make(['account_id' => $this->account->id, 'registers' => $registers])->toArray();
        $this->actingAs($this->user)->ajax()->post($this->route, $location)->assertOk();

        // update
        $location = Location::latest()->first();
        $update   = factory(Location::class)->make(['account_id' => $this->account->id, 'registers' => $registers]);
        $uRes     = $this->actingAs($this->user)->ajax()->put($this->route . $location->id, $update->toArray());
        $uRes->assertOk();

        $location = $location->refresh();
        $this->assertSame($update->name, $location->name);
        $this->assertEquals($update->phone, $location->phone);
        $this->assertEquals($update->address, $location->address);
        $this->assertEquals($rno, $location->registers()->count());

        // delete
        $this->actingAs($this->user)->ajax()->delete($this->route . $location->id)->assertOk();
    }

    public function testMPSLocationValidation()
    {
        $response = $this->actingAs($this->user)->ajax()->post($this->route, []);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
        $response->assertJsonValidationErrors(['name', 'email', 'phone', 'address', 'account_id', 'registers']);
    }
}
