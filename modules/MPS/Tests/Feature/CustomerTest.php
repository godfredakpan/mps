<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Customer;
use Modules\MPS\Tests\MPSTestCase;

class CustomerTest extends MPSTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->user  = $this->createUser('super');
        $this->route = url(module('route')) . '/app/customers/';
    }

    public function testMPSCanCreatAndUpdateCustomer()
    {
        // insert
        $customer = factory(Customer::class)->make(['user_id' => $this->user->id])->toArray();
        $this->actingAs($this->user)->ajax()->post($this->route, $customer)->assertOk();

        // update
        $customer = Customer::latest()->first();
        $update   = factory(Customer::class)->make();
        $response = $this->actingAs($this->user)->ajax()->put($this->route . $customer->id, $update->toArray());
        $response->assertOk();

        $customer = $customer->refresh();
        $this->assertSame($update->name, $customer->name);
        $this->assertEquals($update->email, $customer->email);
        $this->assertEquals($update->phone, $customer->phone);
        $this->assertEquals($update->state, $customer->state);
        $this->assertEquals($update->address, $customer->address);
        $this->assertEquals($update->country, $customer->country);

        // delete
        $this->actingAs($this->user)->ajax()->delete($this->route . $customer->id)->assertOk();
    }

    public function testMPSCustomerValidation()
    {
        $response = $this->actingAs($this->user)->ajax()->post($this->route, []);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
        $response->assertJsonValidationErrors(['name']);
    }
}
