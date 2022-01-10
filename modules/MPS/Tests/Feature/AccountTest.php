<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Account;
use Modules\MPS\Tests\MPSTestCase;

class AccountTest extends MPSTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->user  = $this->createUser('super');
        $this->route = url(module('route')) . '/app/accounts/';
    }

    public function testMPSAccountValidation()
    {
        $response = $this->actingAs($this->user)->ajax()->post($this->route, []);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
        $response->assertJsonValidationErrors(['name', 'type', 'opening_balance']);
    }

    public function testMPSCanCreatAndUpdateAccount()
    {
        // insert
        $account = factory(Account::class)->make()->toArray();
        $this->actingAs($this->user)->ajax()->post($this->route, $account)->assertOk();

        // update
        $account = Account::latest()->first();
        $update  = factory(Account::class)->make();
        $uRes    = $this->actingAs($this->user)->ajax()->put($this->route . $account->id, $update->toArray());
        $uRes->assertOk();

        $account = $account->refresh();
        $this->assertSame($update->name, $account->name);
        $this->assertEquals($update->type, $account->type);
        $this->assertEquals($update->opening_balance, $account->opening_balance);

        // delete
        $this->actingAs($this->user)->ajax()->delete($this->route . $account->id)->assertOk();
    }
}
