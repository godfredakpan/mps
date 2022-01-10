<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Account;
use Modules\MPS\Models\Expense;
use Modules\MPS\Models\Category;
use Modules\MPS\Models\Location;
use Modules\MPS\Models\Permission;
use Modules\MPS\Tests\MPSTestCase;

class ExpensePermissionsTest extends MPSTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->super    = $this->createUser('super');
        $this->staff    = $this->createUser('staff');
        $this->account  = factory(Account::class)->create();
        $this->category = factory(Category::class)->create();
        $this->route    = url(module('route')) . '/app/expenses/';
        $this->location = factory(Location::class)->create(['account_id' => $this->account->id]);
        session(['location_id' => $this->location->id]);

        $this->category->expenses()->saveMany(
            factory(Expense::class, 10)->make([
                'user_id'    => $this->super->id,
                'account_id' => $this->account->id,
            ])
        );

        $this->expenses = Expense::all();
    }

    public function testMPS1SuperPermissionsForExpenseRoute()
    {
        $expense = $this->expenses->first();

        // can access the view route
        $this->actingAs($this->super)->ajax()->get($this->route . $expense->id)->assertOk();

        // can access the store route
        $this->actingAs($this->super)->ajax()->post($this->route, [])->assertStatus(422);

        // can access the update route
        $this->actingAs($this->super)->ajax()->put($this->route . $expense->id, [])->assertStatus(422);

        // can access update route of others
        $this->actingAs($this->super)->ajax()->put($this->route . $expense->id, [])->assertStatus(422);

        // can delete
        $this->actingAs($this->super)->ajax()->delete($this->route . $expense->id)->assertOk();
    }

    public function testMPS3ExpenseRolePermissionsForStaffWithNoPermissions()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $expense = factory(Expense::class)
            ->create([
                'user_id'    => $this->staff->id,
                'account_id' => $this->account->id,
                // 'category_id' => $this->category->id,
            ]);
        $form = factory(Expense::class)
            ->make([
                'user_id'     => $this->staff->id,
                'account_id'  => $this->account->id,
                'category_id' => $this->category->id,
            ])->toArray();

        $this->actingAs($this->staff)->ajax()->get($this->route . $expense->id)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->get($this->route . '?limit=10&byColumn=false&orderBy=date+desc&page=1')->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->post($this->route, $form)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->put($this->route . $expense->id, $form)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->delete($this->route . $expense->id)->assertStatus(403);
    }

    public function testMPS4ExpenseRolePermissionsForStaffWithPermissions()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        // expense
        $role = $this->staff->roles()->first();
        Permission::firstOrCreate(['name' => 'read-expenses'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'update-expenses'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'create-expenses'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'delete-expenses'])->assignRole($role);

        $expense = factory(Expense::class)
            ->create([
                'user_id'    => $this->staff->id,
                'account_id' => $this->account->id,
                // 'category_id' => $this->category->id,
            ]);
        $others = $this->expenses->first();
        $form   = factory(Expense::class)
            ->make([
                'user_id'     => $this->staff->id,
                'account_id'  => $this->account->id,
                'category_id' => $this->category->id,
            ])->toArray();

        $this->actingAs($this->staff)->ajax()->get($this->route . '?limit=10&byColumn=false&orderBy=date+desc&page=1')->assertOk();
        $this->actingAs($this->staff)->ajax()->get($this->route . $expense->id)->assertOk();
        $this->actingAs($this->staff)->ajax()->post($this->route, $form)->assertOk();
        $this->actingAs($this->staff)->ajax()->put($this->route . $expense->id, $form)->assertOk();
        $this->actingAs($this->staff)->ajax()->put($this->route . $others->id, $form)->assertStatus(404);
        $this->actingAs($this->staff)->ajax()->delete($this->route . $expense->id)->assertOk();
    }

    public function testMPS4PublicCannotPerformAnyActionOnExpense()
    {
        $this->ajax()->get($this->route)->assertUnauthorized();
        $this->ajax()->post($this->route, [])->assertUnauthorized();
        $this->ajax()->put($this->route . '1', [])->assertUnauthorized();
        $this->ajax()->delete($this->route . '1')->assertUnauthorized();
    }
}
