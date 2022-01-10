<?php

namespace Tests;

use App\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Notification::fake();

        if (app()->environment('testing')) {
            $modules = array_slice(scandir(__DIR__ . '/../modules'), 2);
            foreach ($modules as $module) {
                app()->register("\Modules\\{$module}\\Providers\\{$module}ServiceProvider");
            }
        }
    }

    public function ajax()
    {
        return $this->withHeaders([
            'HTTP_ACCEPT'           => 'application/json',
            'HTTP_CONTENT_TYPE'     => 'application/json',
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);
    }

    public function create($class, $attributes = [], $times = null)
    {
        return factory($class, $times)->create($attributes);
    }

    public function getUser($username = 'super')
    {
        return User::where('username', $username)->first();
    }

    public function make($class, $attributes = [], $times = null)
    {
        return factory($class, $times)->make($attributes);
    }
}
