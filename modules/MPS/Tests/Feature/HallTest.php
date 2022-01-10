<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Hall;
use Modules\MPS\Models\Account;
use Modules\MPS\Models\Location;
use Modules\MPS\Tests\MPSTestCase;

class HallTest extends MPSTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->user     = $this->createUser('super');
        $this->account  = factory(Account::class)->create();
        $this->route    = url(module('route')) . '/app/halls/';
        $this->location = factory(Location::class)->create(['account_id' => $this->account->id]);
    }

    public function testMPSCanCreatAndUpdateHall()
    {
        // insert
        $hall = factory(Hall::class)->make(['location_id' => $this->location->id])->toArray();
        $this->actingAs($this->user)->ajax()->post($this->route, $hall)->assertOk();

        $hall = Hall::latest()->first();

        // update
        $update = factory(Hall::class)->make(['location_id' => $this->location->id]);
        $uRes   = $this->actingAs($this->user)->ajax()->put($this->route . $hall->id, $update->toArray());
        $uRes->assertOk();

        $hall = $hall->refresh();
        $this->assertSame($update->name, $hall->name);
        $this->assertEquals($update->code, $hall->code);

        // delete
        $this->actingAs($this->user)->ajax()->delete($this->route . $hall->id)->assertOk();
    }

    public function testMPSHallValidation()
    {
        $response = $this->actingAs($this->user)->ajax()->post($this->route, []);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
        $response->assertJsonValidationErrors(['title', 'code', 'location_id']);
    }
}
