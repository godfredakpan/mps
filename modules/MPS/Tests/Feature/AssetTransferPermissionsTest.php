<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Account;
use Modules\MPS\Models\Permission;
use Modules\MPS\Tests\MPSTestCase;
use Modules\MPS\Models\AssetTransfer;

class AssetTransferPermissionsTest extends MPSTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->super    = $this->createUser('super');
        $this->staff    = $this->createUser('staff');
        $this->route    = url(module('route')) . '/app/transfers/asset/';
        $this->account  = factory(Account::class)->create(['opening_balance' => 3000]);
        $this->account2 = factory(Account::class)->create(['opening_balance' => 100]);
        // $this->accounts  = factory(Account::class, 5)->create(['opening_balance' => 0])->pluck('id')->all();
    }

    public function testMPS1SuperPermissionsForAssetTransferRoute()
    {
        // $asset_transfer = $this->asset_transfers->first();
        $asset_transfer = factory(AssetTransfer::class)->make([
            'amount'  => 100,
            'user_id' => $this->super->id,
            'from'    => $this->account->id,
            'to'      => $this->account2->id,
        ])->toArray();
        $this->actingAs($this->super)->ajax()->post($this->route, $asset_transfer)->assertOk();
        $asset_transfer = AssetTransfer::first();

        // can access the view route
        $this->actingAs($this->super)->ajax()->get($this->route . $asset_transfer->id)->assertOk();

        // can access the store route
        $this->actingAs($this->super)->ajax()->post($this->route, [])->assertStatus(422);

        // can access the update route
        $this->actingAs($this->super)->ajax()->put($this->route . $asset_transfer->id, [])->assertStatus(422);

        // can access update route of others
        $this->actingAs($this->super)->ajax()->put($this->route . $asset_transfer->id, [])->assertStatus(422);

        // can delete
        $this->actingAs($this->super)->ajax()->delete($this->route . $asset_transfer->id)->assertOk();
    }

    public function testMPS2AssetTransferRolePermissionsForStaffWithNoPermissions()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $asset_transfer = factory(AssetTransfer::class)
            ->create([
                'amount'  => 100,
                'user_id' => $this->staff->id,
                'from'    => $this->account->id,
                'to'      => $this->account2->id,
            ]);
        $form = factory(AssetTransfer::class)
            ->make([
                'amount'  => 100,
                'user_id' => $this->staff->id,
                'from'    => $this->account->id,
                'to'      => $this->account2->id,
            ])->toArray();

        $this->actingAs($this->staff)->ajax()->get($this->route . $asset_transfer->id)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->get($this->route . '?limit=10&byColumn=false&orderBy=date+desc&page=1')->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->post($this->route, $form)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->put($this->route . $asset_transfer->id, $form)->assertStatus(403);
        $this->actingAs($this->staff)->ajax()->delete($this->route . $asset_transfer->id)->assertStatus(403);
    }

    public function testMPS3AssetTransferRolePermissionsForStaffWithPermissions()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        // asset-transfers
        $role = $this->staff->roles()->first();
        Permission::firstOrCreate(['name' => 'read-asset-transfers'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'update-asset-transfers'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'create-asset-transfers'])->assignRole($role);
        Permission::firstOrCreate(['name' => 'delete-asset-transfers'])->assignRole($role);

        $asset_transfer = factory(AssetTransfer::class)
            ->create([
                'amount'  => 100,
                'user_id' => $this->staff->id,
                'from'    => $this->account->id,
                'to'      => $this->account2->id,
            ]);

        $others = factory(AssetTransfer::class)
            ->create([
                'amount'  => 100,
                'user_id' => $this->super->id,
                'from'    => $this->account->id,
                'to'      => $this->account2->id,
            ]);
        $form = factory(AssetTransfer::class)
            ->make([
                'amount'  => 100,
                'user_id' => $this->staff->id,
                'from'    => $this->account->id,
                'to'      => $this->account2->id,
            ])->toArray();

        $this->actingAs($this->staff)->ajax()->get($this->route . '?limit=10&byColumn=false&orderBy=date+desc&page=1')->assertOk();
        $this->actingAs($this->staff)->ajax()->get($this->route . $asset_transfer->id)->assertOk();
        $this->actingAs($this->staff)->ajax()->post($this->route, $form)->assertOk();
        $this->actingAs($this->staff)->ajax()->put($this->route . $asset_transfer->id, $form)->assertOk();
        $this->actingAs($this->staff)->ajax()->delete($this->route . $asset_transfer->id)->assertOk();

        // Can't update or delete other users record
        $this->actingAs($this->staff)->ajax()->put($this->route . $others->id, $form)->assertStatus(404);
        $this->actingAs($this->staff)->ajax()->delete($this->route . $others->id)->assertStatus(404);
    }

    public function testMPS4PublicCannotPerformAnyActionOnAssetTransfer()
    {
        $this->ajax()->get($this->route)->assertUnauthorized();
        $this->ajax()->post($this->route, [])->assertUnauthorized();
        $this->ajax()->put($this->route . '1', [])->assertUnauthorized();
        $this->ajax()->delete($this->route . '1')->assertUnauthorized();
    }
}
