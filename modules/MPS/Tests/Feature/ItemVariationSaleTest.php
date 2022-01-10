<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Item;
use Modules\MPS\Models\Sale;
use Modules\MPS\Models\Unit;
use Modules\MPS\Models\Brand;
use Modules\MPS\Models\Account;
use Modules\MPS\Models\Costing;
use Modules\MPS\Models\Category;
use Modules\MPS\Models\Customer;
use Modules\MPS\Models\Location;
use Modules\MPS\Models\SaleItem;
use Modules\MPS\Tests\MPSTestCase;
use Modules\MPS\Models\OverSelling;

class ItemVariationSaleTest extends MPSTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->user     = $this->createUser('super');
        $this->unit     = factory(Unit::class)->create();
        $this->brand    = factory(Brand::class)->create();
        $this->account  = factory(Account::class)->create();
        $this->category = factory(Category::class)->create();
        $this->route    = url(module('route')) . '/app/sales/';
        factory('Modules\MPS\Models\Setting')->create(['mps_key' => 'stock', 'mps_value' => '1']);
        $this->locations = factory(Location::class, 2)->create(['account_id' => $this->account->id]);
        $this->items     = factory(Item::class, 5)->create()->each(function ($item) {
            $item->categories()->sync($this->category->id);
            foreach ($this->locations as $location) {
                session(['location_id' => $location->id]);
                $item->stock()->create(['quantity' => 20]);
            }
        });
    }

    public function testMPSVariantStockIsWorkingFine()
    {
        // Check with draft true
        $location = $this->locations->first();
        session(['location_id' => $location->id]);
        $items     = $this->createItemsWithVariants();
        $customer  = factory(Customer::class)->create(['user_id' => $this->user->id]);
        $new_sale1 = factory(Sale::class)->make([
            'draft'       => true,
            'customer_id' => $customer->id,
            'date'        => now()->format('Y-m-d'),
        ])->toArray();

        $items              = collect($items)->random(2);
        $new_sale1['items'] = $items->map(function ($item) use ($location, $customer) {
            $applicable_taxes = $item->taxes->filter(function ($tax) use ($location, $customer) {
                if ($tax->state) {
                    $check = $customer->state == $location->state;
                    return $tax->same ? $check : !$check;
                }
                return true;
            })->pluck('id')->all();
            $variation = null;
            if ($item->has_variants) {
                $variation = $item->variations->random();
            }
            return [
                'id'              => $item->id,
                'name'            => $item->name,
                'code'            => $item->code,
                'cost'            => $item->cost,
                'price'           => $item->price,
                'quantity'        => mt_rand(2, 5),
                'item_id'         => $item->id,
                'batch_no'        => 'bn123',
                'net_cost'        => $item->net_cost,
                'net_price'       => $item->net_price,
                'unit_cost'       => $item->unit_cost,
                'unit_price'      => $item->unit_price,
                'taxes'           => $applicable_taxes,
                'allTaxes'        => $item->taxes->pluck('id')->all(),
                'categories'      => $item->categories[0]->id,
                'selected'        => [],
                'discount_amount' => null,
                'discount'        => null,
                'promotions'      => null,
                'expiry_date'     => now()->format('Y-m-d'),
                'variation_id'    => $variation ? $variation->id : null,
            ];
        })->toArray();

        // insert
        $response = $this->actingAs($this->user)->ajax()->post($this->route, $new_sale1);
        // dd($response->json());
        $response->assertOk();

        // check
        $update = $new_sale1;
        $sale   = Sale::with('items')->find($response['data']['id']);
        $this->assertEquals(1, $sale->draft);
        foreach ($sale->items as $item) {
            if ($item->variations->isNotEmpty()) {
                foreach ($item->variations as $variation) {
                    $this->assertEquals(5, $variation->stock()->first()->quantity);
                }
            }
            $this->assertEquals(40, $item->stock()->first()->quantity);
        }
        $update['draft'] = false;
        $update['date']  = now()->subDays(2)->format('Y-m-d');
        $response        = $this->actingAs($this->user)->ajax()->put($this->route . $sale->id, $update);
        // dd($response->json());
        $response->assertOk();

        // update
        $sale = $sale->refresh();
        $this->assertEquals(0, $sale->draft);
        $this->assertEquals($update['date'], $sale->date->toDateString());
        foreach ($sale->items as $item) {
            $item->refresh();
            if ($item->variations->isNotEmpty()) {
                foreach ($item->variations as $variation) {
                    $this->assertEquals(5 - $variation->pivot->quantity, $variation->stock()->first()->quantity);
                }
            }
            $this->assertEquals(40 - $item->quantity, $item->stock()->first()->quantity);
        }

        // delete
        $this->actingAs($this->user)->ajax()->delete($this->route . $sale->id)->assertOk();
        $this->assertDeleted($sale);
        $this->assertEquals(0, SaleItem::count());
        $items = Item::with('stock')->get();
        foreach ($items as $item) {
            if ($item->variations->isNotEmpty()) {
                foreach ($item->variations as $variation) {
                    $this->assertEquals(5, $variation->stock()->first()->quantity);
                }
            }
            $this->assertEquals($item->has_variants ? 40 : 20, $item->stock()->where('location_id', $location->id)->first()->quantity);
        }

        // Check with draft false
        $new_sale2          = $new_sale1;
        $new_sale2['draft'] = false;
        $response2          = $this->actingAs($this->user)->ajax()->post($this->route, $new_sale2);
        $response2->assertOk();

        $update = $new_sale2;
        $sale   = Sale::with('items')->find($response2['data']['id']);
        $this->assertEquals(0, $sale->draft);
        foreach ($sale->items as $item) {
            if ($item->variations->isNotEmpty()) {
                foreach ($item->variations as $variation) {
                    $this->assertEquals(5 - $variation->pivot->quantity, $variation->stock()->first()->quantity);
                }
            }
            $this->assertEquals(40 - $item->quantity, $item->stock()->first()->quantity);
        }
        $update['draft'] = true;
        $update['date']  = now()->subDays(2)->format('Y-m-d');
        $this->actingAs($this->user)->ajax()->put($this->route . $sale->id, $update)->assertOk();

        // update
        $sale = $sale->refresh();
        $this->assertEquals(1, $sale->draft);
        $this->assertEquals($update['date'], $sale->date->toDateString());
        foreach ($sale->items as $item) {
            $item->refresh();
            if ($item->variations->isNotEmpty()) {
                foreach ($item->variations as $variation) {
                    $this->assertEquals(5, $variation->stock()->first()->quantity);
                }
            }
            $this->assertEquals(40, $item->stock()->first()->quantity);
        }

        $this->actingAs($this->user)->ajax()->delete($this->route . $sale->id)->assertOk();
        $this->assertDeleted($sale);
        $this->assertEquals(0, SaleItem::count());
        $this->assertEquals(0, Costing::count());
        $this->assertEquals(0, OverSelling::count());
        $items = Item::with('stock')->get();
        foreach ($items as $item) {
            if ($item->variations->isNotEmpty()) {
                foreach ($item->variations as $variation) {
                    $this->assertEquals(5, $variation->stock()->first()->quantity);
                }
            }
            $this->assertEquals($item->has_variants ? 40 : 20, $item->stock()->where('location_id', $location->id)->first()->quantity);
        }
    }

    private function createItemsWithVariants()
    {
        $items    = [];
        $variants = [
            ['name' => 'Size', 'options' => ['S', 'M']],
            ['name' => 'Color', 'options' => ['Black', 'White']],
        ];
        for ($i = 1; $i < 5; $i++) {
            $item = factory('Modules\MPS\Models\Item')->create([
                'code'         => 'Item0' . $i,
                'symbology'    => 'code128',
                'name'         => 'Item 0' . $i,
                'variants'     => $variants,
                'has_variants' => 1,
            ]);

            $item->categories()->sync($this->category->id);
            foreach ($this->locations as $location) {
                session(['location_id' => $location->id]);
                $item->stock()->create(['quantity' => 40]);
            }

            $metas = $this->permutations($variants);
            foreach ($metas as $meta) {
                $item_variation = factory('Modules\MPS\Models\Variation')->create(['item_id' => $item->id, 'meta' => $meta, 'code' => 'Variation Color ' . $meta['Color'] . ' Size ' . $meta['Size']]);
                foreach ($this->locations as $location) {
                    session(['location_id' => $location->id]);
                    factory('Modules\MPS\Models\VariationStock')->create(['variation_id' => $item_variation->id, 'location_id' => $location->id, 'quantity' => 5]);
                }
            }

            $modifiers = factory('Modules\MPS\Models\Modifier', mt_rand(3, 6))->create()->each(function ($m) {
                $items = $this->items->unique()->random(mt_rand(3, 5));
                foreach ($items as $item) {
                    $variation = $item->variations()->exists() ? $item->variations()->inRandomOrder()->first() : null;
                    $m->options()->save(factory('Modules\MPS\Models\ModifierOption')->make(['item_id' => $item->id, 'variation_id' => $variation ? $variation->id : null]));
                }
            });
            $item_modifiers = $modifiers->random(mt_rand(1, 3))->pluck('id')->all();
            $item->modifiers()->sync($item_modifiers);
            $items[] = $item;
        }

        return $items;
    }

    private static function permutations(array $array)
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
