<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Tests\MPSTestCase;
use Modules\MPS\Models\CustomerGroup;

class CustomerGroupTest extends MPSTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->user  = $this->createUser('super');
        $this->route = url(module('route')) . '/app/customer_groups/';
    }

    public function testMPSCanCreatAndUpdateCustomerGroup()
    {
        // insert
        $customer_group = factory(CustomerGroup::class)->make()->toArray();
        $this->actingAs($this->user)->ajax()->post($this->route, $customer_group)->assertOk();

        $customer_group = CustomerGroup::latest()->first();

        // update
        $update = factory(CustomerGroup::class)->make();
        $uRes   = $this->actingAs($this->user)->ajax()->put($this->route . $customer_group->id, $update->toArray());
        $uRes->assertOk();

        $customer_group = $customer_group->refresh();
        $this->assertSame($update->name, $customer_group->name);
        $this->assertEquals($update->code, $customer_group->code);
        $this->assertEquals($update->discount, $customer_group->discount);

        // delete
        $this->actingAs($this->user)->ajax()->delete($this->route . $customer_group->id)->assertOk();
    }

    public function testMPSCustomerGroupValidation()
    {
        $response = $this->actingAs($this->user)->ajax()->post($this->route, []);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
        $response->assertJsonValidationErrors(['name', 'code', 'discount']);
    }
}
