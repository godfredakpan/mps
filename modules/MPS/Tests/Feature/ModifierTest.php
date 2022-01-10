<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Item;
use Modules\MPS\Models\Unit;
use Modules\MPS\Models\Brand;
use Modules\MPS\Models\Modifier;
use Illuminate\Support\Facades\DB;
use Modules\MPS\Tests\MPSTestCase;
use Modules\MPS\Models\ModifierOption;

class ModifierTest extends MPSTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->user  = $this->createUser('super');
        $this->unit  = factory(Unit::class)->create();
        $this->brand = factory(Brand::class)->create();
        $this->route = url(module('route')) . '/app/modifiers/';
    }

    public function testMPSCanCreatAndUpdateModifier()
    {
        $options = [];
        $items   = factory(Item::class, mt_rand(2, 4))->create([
            'unit_id'  => $this->unit->id,
            'brand_id' => $this->brand->id,
        ]);
        foreach ($items as $item) {
            $options[] = [
                'item_id'  => $item->id,
                'sku'      => $item->sku,
                'name'     => $item->name,
                'cost'     => $item->cost,
                'price'    => $item->price,
                'quantity' => 1,
            ];
        };
        $modifier            = factory(Modifier::class)->make()->toArray();
        $modifier['options'] = $options;
        // dd($modifier);
        // insert
        $this->actingAs($this->user)->ajax()->post($this->route, $modifier)->assertOk();

        // update
        $modifier          = Modifier::latest()->first();
        $update            = factory(Modifier::class)->make()->toArray();
        $update['options'] = $options;

        $this->actingAs($this->user)->ajax()->put($this->route . $modifier->id, $update)->assertOk();

        $updated = $modifier->fresh();
        $this->assertSame($update['code'], $updated->code);
        $this->assertSame($update['title'], $updated->title);
        $this->assertEquals($items->count(), $updated->options()->count());

        // delete
        $this->actingAs($this->user)->ajax()->delete($this->route . $updated->id)->assertOk();
        $this->assertDeleted($updated);
        $this->assertEquals(0, $updated->options()->count());
        $this->assertEquals(0, ModifierOption::count());

        // Items still exists
        $this->assertEquals($items->count(), DB::table('items')->count());
    }

    public function testMPSModifierValidation()
    {
        $response = $this->actingAs($this->user)->ajax()->post($this->route, ['options' => []]);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
        $response->assertJsonValidationErrors(['code', 'title', 'show_as', 'options']);
    }
}
