<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Item;
use Modules\MPS\Models\Unit;
use Modules\MPS\Models\Brand;
use Modules\MPS\Models\Account;
use Modules\MPS\Models\Portion;
use Modules\MPS\Models\Category;
use Modules\MPS\Models\Location;
use Modules\MPS\Models\Modifier;
use Modules\MPS\Models\Variation;
use Illuminate\Support\Facades\DB;
use Modules\MPS\Tests\MPSTestCase;
use Modules\MPS\Models\PortionItem;
use Modules\MPS\Models\VariationStock;
use Modules\MPS\Models\PortionChoosable;
use Modules\MPS\Models\PortionChoosableItem;

class ItemTest extends MPSTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->user     = $this->createUser('super');
        $this->unit     = factory(Unit::class)->create();
        $this->brand    = factory(Brand::class)->create();
        $this->account  = factory(Account::class)->create();
        $this->category = factory(Category::class)->create();
        $this->route    = url(module('route')) . '/app/items/';
        $this->location = factory(Location::class)->create(['account_id' => $this->account->id]);
    }

    public function testMPSCanCreatAndUpdateItem()
    {
        $variants = [
            ['name' => 'Size', 'options' => ['S', 'M', 'L', 'XL']],
            ['name' => 'Color', 'options' => ['Black', 'Blue', 'Green', 'Red', 'White']],
        ];
        $item = factory(Item::class)->make([
            'unit_id'     => $this->unit->id,
            'brand_id'    => $this->brand->id,
            'category_id' => $this->category->id,
        ])->toArray();
        $item['stock'][] = ['quantity' => 10, 'rack' => 'AB01', 'location_id' => $this->location->id];

        // insert
        $res = $this->actingAs($this->user)->ajax()->post($this->route, $item);
        $res->assertOk();

        // update
        $item     = Item::latest()->first();
        $category = factory(Category::class)->create();
        $update   = factory(Item::class)->make([
            'has_variants' => 1,
            'variants'     => $variants,
            'unit_id'      => $this->unit->id,
            'brand_id'     => $this->brand->id,
            'category_id'  => $this->category->id,
        ])->toArray();
        $update['stock'][] = ['quantity' => 20, 'rack' => 'AB01', 'location_id' => $this->location->id];
        // $update['modifiers'][] = factory(Modifier::class, mt_rand(2, 4))->make()->toArray();
        $mItems = factory(Item::class, mt_rand(2, 4))->create([
            'unit_id'  => $this->unit->id,
            'brand_id' => $this->brand->id,
        ]);
        $modifiers = factory(Modifier::class, mt_rand(2, 4))->create()->each(function ($m) use ($mItems) {
            foreach ($mItems as $item) {
                $variation = $item->variations()->exists() ? $item->variations()->inRandomOrder()->first() : null;
                $m->options()->save(factory('Modules\MPS\Models\ModifierOption')->make(['item_id' => $item->id, 'variation_id' => $variation ? $variation->id : null]));
            }
        });
        $update['modifiers'] = $modifiers->pluck('id')->all();

        $metas = self::permutations($variants);
        foreach ($metas as $meta) {
            $variations = factory(Variation::class)->make([
                'meta' => $meta,
                'code' => 'Variation Color ' . $meta['Color'] . ' Size ' . $meta['Size'],
            ])->toArray();
            $variations['stock'][]  = factory(VariationStock::class)->make(['location_id' => $this->location->id])->toArray();
            $update['variations'][] = $variations;
        }

        $iItems = factory(Item::class, mt_rand(2, 4))->create([
            'unit_id'  => $this->unit->id,
            'brand_id' => $this->brand->id,
        ]);
        $cost    = collect($iItems)->sum('cost');
        $price   = ceil($cost * 1.5);
        $portion = factory(Portion::class)->make(['cost' => $cost, 'price' => $price])->toArray();
        foreach ($iItems as $iItem) {
            $portion['choosables']['name']  = 'Chooable Item';
            $portion['choosables']['items'] = factory(PortionItem::class, mt_rand(2, 3))->make(['id' => $iItem->id])->toArray();
        }
        $update['portions'][] = $portion;

        $response = $this->actingAs($this->user)->ajax()->put($this->route . $item->id, $update);
        // dd($response->json());
        $response->assertOk();

        $updated = $item->fresh();
        $this->assertSame($update['name'], $updated->name);
        $this->assertSame($update['code'], $updated->code);
        $this->assertEquals($modifiers->count(), $updated->modifiers()->count());
        $this->assertEquals(1, $updated->portions()->count());
        $this->assertSame($update['unit_id'], $updated->unit_id);
        $this->assertEquals(20, $updated->variations()->count());
        $this->assertSame($update['brand_id'], $updated->brand_id);
        $this->assertEquals($update['stock'][0]['quantity'], $updated->stock->first()->quantity);

        // $this->assertDatabaseHas('item_modifier', ['item_id' => $updated->id]);

        // delete
        $this->actingAs($this->user)->ajax()->delete($this->route . $updated->id)->assertOk();
        $this->assertDeleted($updated);
        $this->assertDeleted($updated->stock->first());
        $this->assertEquals(0, Portion::count());
        $this->assertEquals(0, Variation::count());
        $this->assertEquals(0, PortionChoosable::count());
        $this->assertEquals(0, PortionChoosableItem::count());
        $this->assertEquals(0, DB::table('item_modifier')->count());

        // Location still exists
        $this->assertEquals(1, Location::count());
        $this->assertGreaterThan(1, DB::table('items')->count());
        $this->assertGreaterThan(1, DB::table('modifiers')->count());
    }

    public function testMPSItemValidation()
    {
        $response = $this->actingAs($this->user)->ajax()->post($this->route, []);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
        $response->assertJsonValidationErrors(['code', 'name', 'symbology', 'cost', 'price', 'category_id']);
    }

    protected static function permutations(array $array)
    {
        $metas  = [];
        $sizes  = $array[0]['options'];
        $colors = $array[1]['options'];
        foreach ($colors as $color) {
            foreach ($sizes as $size) {
                $metas[] = ['Color' => $color, 'Size' => $size];
            }
        }
        return $metas;
    }
}
