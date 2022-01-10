<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Category;
use Modules\MPS\Tests\MPSTestCase;

class CategoryTest extends MPSTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->user  = $this->createUser('super');
        $this->route = url(module('route')) . '/app/categories/';
    }

    public function testMPSCanCreatAndUpdateCategory()
    {
        // insert
        $category = factory(Category::class)->make()->toArray();
        $this->actingAs($this->user)->ajax()->post($this->route, $category)->assertOk();

        $category    = Category::latest()->first();
        $subcategory = factory(Category::class)->make(['parent_id' => $category->id])->toArray();
        $this->actingAs($this->user)->ajax()->post($this->route, $subcategory)->assertOk();

        // check subcategoy parent
        $subcategory = Category::where('id', '!=', $category->id)->first();
        $this->assertSame($category->id, $subcategory->parent_id);

        // update
        $update = factory(Category::class)->make();
        $uRes   = $this->actingAs($this->user)->ajax()->put($this->route . $category->id, $update->toArray());
        $uRes->assertOk();

        $category = $category->refresh();
        $this->assertSame($update->name, $category->name);
        $this->assertEquals($update->code, $category->code);

        // update subcategory
        $nCat = factory(Category::class)->create();
        $sCat = factory(Category::class)->make(['parent_id' => $nCat->id]);
        $sRes = $this->actingAs($this->user)->ajax()->put($this->route . $subcategory->id, $sCat->toArray());

        $sRes->assertOk();
        $subcategory = $subcategory->refresh();
        $this->assertSame($sCat->name, $subcategory->name);
        $this->assertEquals($sCat->code, $subcategory->code);
        $this->assertSame($nCat->id, $subcategory->parent_id);

        // delete
        $this->actingAs($this->user)->ajax()->delete($this->route . $category->id)->assertOk();
    }

    public function testMPSCategoryValidation()
    {
        $response = $this->actingAs($this->user)->ajax()->post($this->route, []);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
        $response->assertJsonValidationErrors(['name', 'code']);
    }
}
