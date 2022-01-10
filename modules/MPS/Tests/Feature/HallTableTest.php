<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Hall;
use Modules\MPS\Models\Account;
use Modules\MPS\Models\Location;
use Modules\MPS\Models\HallTable;
use Modules\MPS\Tests\MPSTestCase;

class HallTableTest extends MPSTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->user     = $this->createUser('super');
        $this->account  = factory(Account::class)->create();
        $this->route    = url(module('route')) . '/app/tables/';
        $this->location = factory(Location::class)->create(['account_id' => $this->account->id]);
        $this->hall     = factory(Hall::class)->create(['location_id' => $this->location->id]);
    }

    public function testMPSCanCreatAndUpdateHallTable()
    {
        // insert
        $hall_table = factory(HallTable::class)->make(['hall_id' => $this->hall->id])->toArray();
        $this->actingAs($this->user)->ajax()->post($this->route, $hall_table)->assertOk();

        $hall_table = HallTable::latest()->first();

        // update
        $update = factory(HallTable::class)->make(['hall_id' => $this->hall->id]);
        $uRes   = $this->actingAs($this->user)->ajax()->put($this->route . $hall_table->id, $update->toArray());
        $uRes->assertOk();

        $hall_table = $hall_table->refresh();
        $this->assertSame($update->name, $hall_table->name);
        $this->assertEquals($update->code, $hall_table->code);

        // delete
        $this->actingAs($this->user)->ajax()->delete($this->route . $hall_table->id)->assertOk();
    }

    public function testMPSHallTableValidation()
    {
        $response = $this->actingAs($this->user)->ajax()->post($this->route, []);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
        $response->assertJsonValidationErrors(['title', 'code', 'hall_id']);
    }
}
