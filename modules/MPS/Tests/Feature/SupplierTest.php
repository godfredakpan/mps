<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Supplier;
use Modules\MPS\Tests\MPSTestCase;

class SupplierTest extends MPSTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->user  = $this->createUser('super');
        $this->route = url(module('route')) . '/app/suppliers/';
    }

    public function testMPSCanCreatAndUpdateSupplier()
    {
        // insert
        $supplier = factory(Supplier::class)->make(['user_id' => $this->user->id])->toArray();
        $this->actingAs($this->user)->ajax()->post($this->route, $supplier)->assertOk();

        // update
        $supplier = Supplier::latest()->first();
        $update   = factory(Supplier::class)->make();
        $uRes     = $this->actingAs($this->user)->ajax()->put($this->route . $supplier->id, $update->toArray());
        $uRes->assertOk();

        $supplier = $supplier->refresh();
        $this->assertSame($update->name, $supplier->name);
        $this->assertEquals($update->email, $supplier->email);
        $this->assertEquals($update->phone, $supplier->phone);
        $this->assertEquals($update->state, $supplier->state);
        $this->assertEquals($update->address, $supplier->address);
        $this->assertEquals($update->country, $supplier->country);

        // delete
        $this->actingAs($this->user)->ajax()->delete($this->route . $supplier->id)->assertOk();
    }

    public function testMPSSupplierValidation()
    {
        $response = $this->actingAs($this->user)->ajax()->post($this->route, []);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
        $response->assertJsonValidationErrors(['name']);
    }
}
