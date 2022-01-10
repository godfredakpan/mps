<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Models\Account;
use Modules\MPS\Tests\MPSTestCase;

class AccountPermissionsTest extends MPSTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->super = $this->createUser('super');
        $this->admin = $this->createUser('admin');
        $this->staff = $this->createUser('staff');
        $this->route = url(module('route')) . '/app/accounts/';

        $this->accounts = factory(Account::class)->create();
    }

    public function testMPS1SuperPermissionsForAccountRoute()
    {
        $account = $this->accounts->first();

        // can access the view route
        $this->actingAs($this->super)->ajax()->get($this->route . $account->id)->assertOk();

        // can access the store route
        $this->actingAs($this->super)->ajax()->post($this->route, [])->assertStatus(422);

        // can access the update route
        $this->actingAs($this->super)->ajax()->put($this->route . $account->id, [])->assertStatus(422);

        // can access update route of others
        $this->actingAs($this->super)->ajax()->put($this->route . $account->id, [])->assertStatus(422);

        // can delete
        $this->actingAs($this->super)->ajax()->delete($this->route . $account->id)->assertOk();
    }

    public function testMPS3StaffPermissionsForAccountRoute()
    {
        $account = $this->accounts->first();

        // cannot access the view route
        $this->actingAs($this->staff)->ajax()->get($this->route . $account->id)->assertStatus(403);

        // cannot access the store route
        $this->actingAs($this->staff)->ajax()->post($this->route, [])->assertStatus(403);

        // cannot access the update route
        $this->actingAs($this->staff)->ajax()->put($this->route . $account->id, [])->assertStatus(403);

        // cannot delete
        $this->actingAs($this->staff)->ajax()->delete($this->route . $account->id)->assertStatus(403);
    }

    public function testMPS4PublicCannotPerformAnyActionOnAccount()
    {
        $this->ajax()->get($this->route)->assertUnauthorized();
        $this->ajax()->post($this->route, [])->assertUnauthorized();
        $this->ajax()->put($this->route . '1', [])->assertUnauthorized();
        $this->ajax()->delete($this->route . '1')->assertUnauthorized();
    }
}
