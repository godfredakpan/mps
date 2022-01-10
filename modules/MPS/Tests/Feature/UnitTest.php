<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Unit;
use Modules\MPS\Tests\MPSTestCase;

class UnitTest extends MPSTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->user  = $this->createUser('super');
        $this->route = url(module('route')) . '/app/units/';
    }

    public function testMPSCanCreatAndUpdateUnit()
    {
        // insert
        $unit = factory(Unit::class)->make()->toArray();
        $this->actingAs($this->user)->ajax()->post($this->route, $unit)->assertOk();

        $unit = Unit::latest()->first();

        // update
        $update = factory(Unit::class)->make();
        $uRes   = $this->actingAs($this->user)->ajax()->put($this->route . $unit->id, $update->toArray());
        $uRes->assertOk();

        $unit = $unit->refresh();
        $this->assertSame($update->name, $unit->name);
        $this->assertEquals($update->code, $unit->code);

        // delete
        $this->actingAs($this->user)->ajax()->delete($this->route . $unit->id)->assertOk();
    }

    public function testMPSUnitValidation()
    {
        $response = $this->actingAs($this->user)->ajax()->post($this->route, []);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
        $response->assertJsonValidationErrors(['name', 'code']);
    }
}
