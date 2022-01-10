<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Account;
use Modules\MPS\Tests\MPSTestCase;
use Modules\MPS\Models\AssetTransfer;

class AssetTransferTest extends MPSTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->user     = $this->createUser('super');
        $this->account1 = factory(Account::class)->create(['opening_balance' => 300]);
        $this->account2 = factory(Account::class)->create(['opening_balance' => 100]);
        $this->route    = url(module('route')) . '/app/transfers/asset/';
    }

    public function testMPSAssetTransferValidation()
    {
        $response = $this->actingAs($this->user)->ajax()->post($this->route, []);
        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
        $response->assertJsonValidationErrors(['to', 'from', 'amount']);
    }

    public function testMPSCanCreatAndUpdateAssetTransfer()
    {
        // insert
        $asset_transfer = factory(AssetTransfer::class)->make([
            'amount' => 100,
            'to'     => $this->account2->id,
            'from'   => $this->account1->id,
        ])->toArray();
        $this->actingAs($this->user)->ajax()->post($this->route, $asset_transfer)->assertOk();
        // $this->account1->refresh();
        // $this->account2->refresh();
        $this->assertEquals($this->account1->journal->getBalance(), $this->account2->journal->getBalance());

        // update
        $asset_transfer = AssetTransfer::first();
        $update         = factory(AssetTransfer::class)->make([
            'amount' => 100,
            'to'     => $this->account2->id,
            'from'   => $this->account1->id,
        ]);
        $this->actingAs($this->user)->ajax()->put($this->route . $asset_transfer->id, $update->toArray())->assertOk();

        // TODO
        // $this->assertEquals(30000, $this->account2->journal->getBalance());
        // $this->assertEquals(10000, $this->account1->journal->getBalance());

        $asset_transfer = $asset_transfer->refresh();
        $this->assertEquals($update->to, $asset_transfer->to);
        $this->assertEquals($update->from, $asset_transfer->from);
        $this->assertEquals($update->details, $asset_transfer->details);

        // delete
        $this->actingAs($this->user)->ajax()->delete($this->route . $asset_transfer->id)->assertOk();
        $this->assertDeleted($asset_transfer);
        // $this->account1->refresh();
        // $this->account2->refresh();
        // $this->assertSame(30000, $this->account1->journal->getBalance());
        // $this->assertSame(10000, $this->account2->journal->getBalance());
    }
}
