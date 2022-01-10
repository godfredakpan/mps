<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Income;
use Modules\MPS\Models\Account;
use Modules\MPS\Models\Category;
use Modules\MPS\Models\Location;
use Modules\MPS\Tests\MPSTestCase;

class IncomeTest extends MPSTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->user     = $this->createUser('super');
        $this->account  = factory(Account::class)->create();
        $this->category = factory(Category::class)->create();
        $this->route    = url(module('route')) . '/app/incomes/';
        $this->location = factory(Location::class)->create(['account_id' => $this->account->id]);
    }

    public function testMPSCanCreatAndUpdateIncome()
    {
        // insert
        $income = factory(Income::class)
            ->make([
                'user_id'     => $this->user->id,
                'account_id'  => $this->account->id,
                'category_id' => $this->category->id,
            ])->toArray();
        $this->actingAs($this->user)
            ->withSession(['location_id' => $this->location->id])
            ->ajax()->post($this->route, $income)->assertOk();

        // update
        $income   = Income::latest()->first();
        $category = factory(Category::class)->create();
        $account  = factory(Account::class)->create();
        $update   = factory(Income::class)->make(['account_id' => $account->id, 'category_id' => $category->id]);
        $response = $this->actingAs($this->user)->ajax()->put($this->route . $income->id, $update->toArray());
        $response->assertOk();
        $response->assertSessionHas('location_id', $this->location->id);

        $income = $income->refresh();
        $this->assertSame($update->title, $income->title);
        $this->assertEquals($update->amount, $income->amount);

        $updated_category = $income->categories->first();
        $this->assertEquals($account->id, $income->account_id);
        $this->assertEquals($category->id, $updated_category->id);

        // delete
        $this->actingAs($this->user)->ajax()->delete($this->route . $income->id)->assertOk();
    }

    public function testMPSIncomeValidation()
    {
        $response = $this->actingAs($this->user)
            ->withSession(['location_id' => $this->location->id])
            ->ajax()->post($this->route, []);
        $response->assertSessionHas('location_id', $this->location->id);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
        $response->assertJsonValidationErrors(['title', 'amount', 'account_id', 'category_id']);
    }
}
