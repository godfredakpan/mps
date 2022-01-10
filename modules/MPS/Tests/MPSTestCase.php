<?php

namespace Modules\MPS\Tests;

use Tests\TestCase;
use Modules\MPS\Models\User;

abstract class MPSTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function createUser($username = 'super')
    {
        $role = \App\Role::create(['name' => $username]);
        $user = factory('App\User')->create([
            'name'     => $username,
            'username' => $username,
            'email'    => $username . '@tecdiary.com',
            'password' => bcrypt('123456'),
        ]);
        $user->assignRole($username);
        $user = User::find($user->id);
        $user->assignRole($username);
        return $user;
    }
}
