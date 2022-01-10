<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Salary;
use Modules\MPS\Models\Account;
use Modules\MPS\Models\Location;
use Modules\MPS\Models\Permission;
use Modules\MPS\Tests\MPSTestCase;
use Illuminate\Support\Facades\Auth;

class SalaryPermissionsTest extends MPSTestCase
{
    use TestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->super    = $this->createUser('super');
        $this->staff    = $this->createUser('staff');
        $this->account  = factory(Account::class)->create(['opening_balance' => 100]);
        $this->location = factory(Location::class)->create(['account_id' => $this->account->id]);
        $this->route    = url(module('route')) . '/app/salaries/';

        factory('Modules\MPS\Models\Setting')->create(['mps_key' => 'default_account', 'mps_value' => $this->account->id]);
        factory('Modules\MPS\Models\Setting')->create(['mps_key' => 'loyalty', 'mps_value' => json_encode(['staff' => ['spent' => 100, 'points' => 1], 'customer' => ['spent' => 100, 'points' => 2]])]);
    }

    public function testMPS1SalaryPermissionsForSuperUser()
    {
        $salary = $this->staff->salaries()->create(factory(Salary::class)->make([
            'advance' => 0,
            'points'  => 20,
            'amount'  => 20,
            'status'  => 'paid',
            'type'    => 'commission',
            // 'user_id'    => $this->staff->id,
            'account_id' => $this->account->id,
            'date'       => now()->format('Y-m-d'),
        ])->toArray());

        // can access the view route
        $this->actingAs($this->super)->ajax()->get($this->route . $salary->id)->assertOk();

        // can access the store route
        $this->actingAs($this->super)->ajax()->post($this->route, [])->assertStatus(422);

        // can access the update route
        $this->actingAs($this->super)->ajax()->put($this->route . $salary->id, [])->assertStatus(422);

        // can access update route of others
        $this->actingAs($this->super)->ajax()->put($this->route . $salary->id, [])->assertStatus(422);

        // can delete
        $this->actingAs($this->super)->ajax()->delete($this->route . $salary->id)->assertOk();
    }

    public function testMPS2SalaryPermissionsForStaffWithNoPermissions()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $form = factory(Salary::class)->make([
            'advance'    => 0,
            'points'     => 0,
            'amount'     => 20,
            'status'     => 'paid',
            'type'       => 'salary',
            'user_id'    => $this->staff->id,
            'account_id' => $this->account->id,
            'date'       => now()->format('Y-m-d'),
        ])->toArray();
        $salary = $this->staff->salaries()->create($form);

        $this->actingAs($this->staff)->ajax()->get($this->route . $salary->id)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->get($this->route . '?limit=10&byColumn=false&orderBy=date+desc&page=1')->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->post($this->route, $form)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->put($this->route . $salary->id, $form)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->delete($this->route . $salary->id)->assertStatus(403);
        Auth::logout($this->super);
    }

    public function testMPS3SalaryPermissionsForStaffWithPermissions()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        // salaries
        $role = $this->staff->roles()->first();
        Permission::firstOrCreate(['name' => 'read-salaries'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'create-salaries'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'update-salaries'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'delete-salaries'])->assignRole($role);

        $form = factory(Salary::class)->make([
            'advance'    => 0,
            'points'     => 0,
            'amount'     => 20,
            'status'     => 'paid',
            'type'       => 'salary',
            'user_id'    => $this->staff->id,
            'account_id' => $this->account->id,
            'date'       => now()->format('Y-m-d'),
        ])->toArray();
        $salary = $this->staff->salaries()->create($form);
        $others = $this->super->salaries()->create(factory(Salary::class)->make([
            'advance'    => 0,
            'points'     => 0,
            'amount'     => 20,
            'status'     => 'paid',
            'type'       => 'salary',
            'user_id'    => $this->super->id,
            'account_id' => $this->account->id,
            'date'       => now()->format('Y-m-d'),
        ])->toArray());

        $this->actingAs($this->staff)->ajax()->get($this->route . '?limit=10&byColumn=false&orderBy=date+desc&page=1')->assertOk();
        $this->actingAs($this->staff)->ajax()->get($this->route . $salary->id)->assertOk();
        $this->actingAs($this->staff)->ajax()->post($this->route, $form)->assertOk();
        $this->actingAs($this->staff)->ajax()->put($this->route . $salary->id, $form)->assertOk();
        $this->actingAs($this->staff)->ajax()->delete($this->route . $salary->id)->assertOk();

        // Can't update or delete other users record
        $this->actingAs($this->staff)->ajax()->put($this->route . $others->id, $form)->assertStatus(404);
        $this->actingAs($this->staff)->ajax()->delete($this->route . $others->id)->assertStatus(404);
    }

    public function testMPS4PublicCannotPerformAnyActionOnSale()
    {
        $this->ajax()->get($this->route)->assertUnauthorized();
        $this->ajax()->post($this->route, [])->assertUnauthorized();
        $this->ajax()->put($this->route . '1', [])->assertUnauthorized();
        $this->ajax()->delete($this->route . '1')->assertUnauthorized();
    }
}
