<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Brand;
use Modules\MPS\Tests\MPSTestCase;

class BrandTest extends MPSTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->user  = $this->createUser('super');
        $this->route = url(module('route')) . '/app/brands/';
    }

    public function testMPSBrandValidation()
    {
        $response = $this->actingAs($this->user)->ajax()->post($this->route, []);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
        $response->assertJsonValidationErrors(['name']);
    }

    public function testMPSCanCreateAndUpdateBrand()
    {
        // insert
        $brand = factory(Brand::class)->make()->toArray();
        $this->actingAs($this->user)->ajax()->post($this->route, $brand)->assertOk();

        // update
        $brand  = Brand::latest()->first();
        $update = factory(Brand::class)->make();
        $uRes   = $this->actingAs($this->user)->ajax()->put($this->route . $brand->id, $update->toArray());
        $uRes->assertOk();

        $brand = $brand->refresh();
        $this->assertSame($update->name, $brand->name);
        $this->assertEquals($update->code, $brand->code);
        $this->assertEquals($update->order, $brand->order);
        $this->assertEquals($update->details, $brand->details);

        // delete
        $this->actingAs($this->user)->ajax()->delete($this->route . $brand->id)->assertOk();
    }
}
