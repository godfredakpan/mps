<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Tax;
use Modules\MPS\Tests\MPSTestCase;

class TaxTest extends MPSTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->user  = $this->createUser('super');
        $this->route = url(module('route')) . '/app/taxes/';
    }

    public function testMPSCanCreatAndUpdateTax()
    {
        // insert
        $tax = factory(Tax::class)->make()->toArray();
        $this->actingAs($this->user)->ajax()->post($this->route, $tax)->assertOk();

        $tax    = Tax::latest()->first();
        $subtax = factory(Tax::class)->make(['parent_id' => $tax->id])->toArray();
        $this->actingAs($this->user)->ajax()->post($this->route, $subtax)->assertOk();

        // update
        $update = factory(Tax::class)->make();
        $uRes   = $this->actingAs($this->user)->ajax()->put($this->route . $tax->id, $update->toArray());
        $uRes->assertOk();

        $tax = $tax->refresh();
        $this->assertSame($update->name, $tax->name);
        $this->assertEquals($update->code, $tax->code);

        // delete
        $this->actingAs($this->user)->ajax()->delete($this->route . $tax->id)->assertOk();
    }

    public function testMPSTaxValidation()
    {
        $response = $this->actingAs($this->user)->ajax()->post($this->route, []);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
        $response->assertJsonValidationErrors(['name', 'code']);
    }
}
