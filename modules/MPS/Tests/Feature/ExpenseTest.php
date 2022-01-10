<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Account;
use Modules\MPS\Models\Expense;
use Modules\MPS\Models\Category;
use Modules\MPS\Models\Location;
use Modules\MPS\Tests\MPSTestCase;

class ExpenseTest extends MPSTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->user     = $this->createUser('super');
        $this->account  = factory(Account::class)->create();
        $this->category = factory(Category::class)->create();
        $this->route    = url(module('route')) . '/app/expenses/';
        $this->location = factory(Location::class)->create(['account_id' => $this->account->id]);
    }

    public function testMPSCanCreatAndUpdateExpense()
    {
        // insert
        $expense = factory(Expense::class)
            ->make(['account_id' => $this->account->id, 'category_id' => $this->category->id, 'user_id' => $this->user->id])->toArray();
        $this->actingAs($this->user)
            ->withSession(['location_id' => $this->location->id])
            ->ajax()->post($this->route, $expense)->assertOk();

        // update
        $expense  = Expense::latest()->first();
        $category = factory(Category::class)->create();
        $account  = factory(Account::class)->create();
        $update   = factory(Expense::class)->make(['account_id' => $account->id, 'category_id' => $category->id]);
        $uRes     = $this->actingAs($this->user)->ajax()->put($this->route . $expense->id, $update->toArray());
        $uRes->assertOk();
        $uRes->assertSessionHas('location_id', $this->location->id);

        $expense = $expense->refresh();
        $this->assertSame($update->title, $expense->title);
        $this->assertEquals($update->amount, $expense->amount);

        $updated_category = $expense->categories->first();
        $this->assertEquals($account->id, $expense->account_id);
        $this->assertEquals($category->id, $updated_category->id);

        // delete
        $this->actingAs($this->user)->ajax()->delete($this->route . $expense->id)->assertOk();
    }

    public function testMPSExpenseValidation()
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
