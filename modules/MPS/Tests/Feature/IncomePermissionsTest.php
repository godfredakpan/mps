<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Income;
use Modules\MPS\Models\Account;
use Modules\MPS\Models\Category;
use Modules\MPS\Models\Location;
use Modules\MPS\Models\Permission;
use Modules\MPS\Tests\MPSTestCase;

class IncomePermissionsTest extends MPSTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->super    = $this->createUser('super');
        $this->staff    = $this->createUser('staff');
        $this->account  = factory(Account::class)->create();
        $this->category = factory(Category::class)->create();
        $this->route    = url(module('route')) . '/app/incomes/';
        $this->location = factory(Location::class)->create(['account_id' => $this->account->id]);
        session(['location_id' => $this->location->id]);

        $this->category->incomes()->saveMany(
            factory(Income::class, 5)->make([
                'user_id'    => $this->super->id,
                'account_id' => $this->account->id,
            ])
        );

        $this->incomes = Income::all();
    }

    public function testMPS1SuperPermissionsForIncomeRoute()
    {
        $income = $this->incomes->first();

        // can access the view route
        $this->actingAs($this->super)->ajax()->get($this->route . $income->id)->assertOk();

        // can access the store route
        $this->actingAs($this->super)->ajax()->post($this->route, [])->assertStatus(422);

        // can access the update route
        $this->actingAs($this->super)->ajax()->put($this->route . $income->id, [])->assertStatus(422);

        // can access update route of others
        $this->actingAs($this->super)->ajax()->put($this->route . $income->id, [])->assertStatus(422);

        // can delete
        $this->actingAs($this->super)->ajax()->delete($this->route . $income->id)->assertOk();
    }

    public function testMPS2IncomeRolePermissionsForStaffWithNoPermissions()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $income = factory(Income::class)
            ->create([
                'user_id'    => $this->staff->id,
                'account_id' => $this->account->id,
                // 'category_id' => $this->category->id,
            ]);
        $form = factory(Income::class)
            ->make([
                'user_id'     => $this->staff->id,
                'account_id'  => $this->account->id,
                'category_id' => $this->category->id,
            ])->toArray();

        $this->actingAs($this->staff)->ajax()->get($this->route . $income->id)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->get($this->route . '?limit=10&byColumn=false&orderBy=date+desc&page=1')->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->post($this->route, $form)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->put($this->route . $income->id, $form)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->delete($this->route . $income->id)->assertStatus(403);
    }

    public function testMPS3IncomeRolePermissionsForStaffWithPermissions()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        // income
        $role = $this->staff->roles()->first();
        Permission::firstOrCreate(['name' => 'read-incomes'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'update-incomes'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'create-incomes'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'delete-incomes'])->assignRole($role);

        $income = factory(Income::class)
            ->create([
                'user_id'    => $this->staff->id,
                'account_id' => $this->account->id,
                // 'category_id' => $this->category->id,
            ]);
        $others = $this->incomes->first();
        $form   = factory(Income::class)
            ->make([
                'user_id'     => $this->staff->id,
                'account_id'  => $this->account->id,
                'category_id' => $this->category->id,
            ])->toArray();

        $this->actingAs($this->staff)->ajax()->get($this->route . '?limit=10&byColumn=false&orderBy=date+desc&page=1')->assertOk();
        $this->actingAs($this->staff)->ajax()->get($this->route . $income->id)->assertOk();
        $this->actingAs($this->staff)->ajax()->post($this->route, $form)->assertOk();
        $this->actingAs($this->staff)->ajax()->put($this->route . $income->id, $form)->assertOk();
        $this->actingAs($this->staff)->ajax()->put($this->route . $others->id, $form)->assertStatus(404);
        $this->actingAs($this->staff)->ajax()->delete($this->route . $income->id)->assertOk();
    }

    public function testMPS4PublicCannotPerformAnyActionOnIncome()
    {
        $this->ajax()->get($this->route)->assertUnauthorized();
        $this->ajax()->post($this->route, [])->assertUnauthorized();
        $this->ajax()->put($this->route . '1', [])->assertUnauthorized();
        $this->ajax()->delete($this->route . '1')->assertUnauthorized();
    }
}
