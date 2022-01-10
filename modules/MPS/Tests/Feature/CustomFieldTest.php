<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Field;
use Modules\MPS\Tests\MPSTestCase;

class CustomFieldTest extends MPSTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->user  = $this->createUser('super');
        $this->route = url(module('route')) . '/app/fields/';
    }

    public function testMPSCanCreatAndUpdateField()
    {
        // insert
        $field = factory(Field::class)->make()->toArray();
        $this->actingAs($this->user)->ajax()->post($this->route, $field)->assertOk();

        $field = Field::latest()->first();

        // update
        $update = factory(Field::class)->make();
        $uRes   = $this->actingAs($this->user)->ajax()->put($this->route . $field->id, $update->toArray());
        $uRes->assertOk();

        $field  = $field->refresh();
        $fields = extra_attributes('sale');
        $this->assertSame($update->name, $field->name);
        $this->assertEquals($update->type, $field->type);
        $this->assertEquals($update->slug, $field->slug);
        $this->assertEquals($field->name, $fields[0]->name);
        $this->assertEquals($field->type, $fields[0]->type);
        $this->assertEquals($field->slug, $fields[0]->slug);

        // delete
        $this->actingAs($this->user)->ajax()->delete($this->route . $field->id)->assertOk();
    }

    public function testMPSFieldValidation()
    {
        $response = $this->actingAs($this->user)->ajax()->post($this->route, []);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
        $response->assertJsonValidationErrors(['name', 'type', 'slug', 'entities']);
    }
}
