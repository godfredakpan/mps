<?php

namespace Modules\MPS\Tests\Feature;

use Modules\MPS\Tests\MPSTestCase;
use Illuminate\Support\Facades\Hash;

class UserTest extends MPSTestCase
{
    public function testMPSNonAdminCannotUpdateOtherUser()
    {
        $user1 = $this->createUser('staff');
        $user2 = factory('App\User')->create();

        $this->actingAs($user1)->ajax()->put(url(module('route')) . '/app/users/' . $user2->id, ['name' => 'User 2'])->assertForbidden();
    }

    public function testMPSNonAdminCanUpdateOwnProfile()
    {
        $user = $this->createUser('staff');
        $this->actingAs($user)->ajax()->post(url(module('route')) . '/app/profile', ['name' => 'User Name', 'phone' => '0123456789'])->assertOk();
        $user->refresh();
        $this->assertSame('User Name', $user->name);
        $this->assertSame('0123456789', $user->phone);
    }

    public function testMPSNonSuperCanChangeTheirPassword()
    {
        $user = $this->createUser('staff');
        $this->assertTrue(Hash::check('123456', $user->password));
        $this->actingAs($user)->ajax()->post(url(module('route')) . '/app/profile/change_password', [
            'current'               => '123456',
            'password'              => '0123456789',
            'password_confirmation' => '0123456789',
        ])->assertOk();
        $user->refresh();
        $this->assertTrue(Hash::check('0123456789', $user->password));
    }

    public function testMPSNonSuperCannotAddUser()
    {
        $user = $this->createUser('staff');
        $this->actingAs($user)->ajax()->post(url(module('route')) . '/app/users', [])->assertForbidden();
    }

    public function testMPSSuperCanAddAndUpdateUser()
    {
        $super = $this->createUser('super');
        $user  = factory('App\User')->make()->toArray();

        $user['files']    = [];
        $user['roles']    = ['super'];
        $user['password'] = '12345678';

        $user['password_confirmation'] = '12345678';
        $this->actingAs($super)->ajax()->post(url(module('route')) . '/app/users', $user)->assertOk();
    }

    public function testMPSUserValidation()
    {
        $super    = $this->createUser('super');
        $response = $this->actingAs($super)->ajax()->post(url(module('route')) . '/app/users', []);

        $response->assertStatus(422);
        $res = json_decode($response->getContent());

        $this->assertObjectHasAttribute('errors', $res);
        $this->assertObjectHasAttribute('message', $res);

        $this->assertIsArray($res->errors->name);
        $this->assertIsArray($res->errors->email);
        $this->assertIsArray($res->errors->roles);
        $this->assertIsArray($res->errors->username);
        $this->assertIsArray($res->errors->password);
    }
}
