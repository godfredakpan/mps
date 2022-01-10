<?php

namespace Modules\MPS\Tests\Feature;

use Illuminate\Support\Str;
use Modules\MPS\Tests\MPSTestCase;

class SettingTest extends MPSTestCase
{
    public $route;

    public $super;

    protected function setUp(): void
    {
        parent::setUp();
        $this->super = $this->createUser('super');
        $this->route = url(module('route')) . '/app/settings';
    }

    public function testMPSNonSuperCannotUpdateSettings()
    {
        $user = $this->createUser('admin');
        $this->actingAs($user)->ajax()->post($this->route, [])->assertStatus(403);
    }

    public function testMPSSuperCanAddSettings()
    {
        $settings = ['testing' => 1, 'testing_user' => $this->super->id, 'name' => 'MPS', 'timezone' => 'Asia/Kuala_Lumpur'];
        $this->actingAs($this->super)->ajax()->post($this->route, $settings)->assertOk();

        $this->assertEquals(mps_config('testing'), $settings['testing']);
        $this->assertEquals(mps_config('testing_user'), $settings['testing_user']);
    }

    public function testMPSSuperCanUpdateSettings()
    {
        $settings = ['TEST' => 'TEST'];
        $this->actingAs($this->super)->ajax()->put($this->route, $settings)->assertOk();
        $this->actingAs($this->super)->ajax()->put($this->route, $settings)->assertOk();
        $test = file_get_contents(base_path('.env.' . $this->app->environment()));
        $this->assertTrue(Str::contains($test, 'TEST=TEST'));
    }
}
