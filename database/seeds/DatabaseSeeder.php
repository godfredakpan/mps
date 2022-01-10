<?php

use App\Role;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $faker = Factory::create();
        echo "Creating Settings\n";
        factory('App\Setting')->create(['tec_key' => 'name', 'tec_value' => 'Modern POS Solution']);
        factory('App\Setting')->create(['tec_key' => 'short_name', 'tec_value' => 'MPS']);
        factory('App\Setting')->create(['tec_key' => 'timezone', 'tec_value' => 'Asia/Kuala_Lumpur']);
        factory('App\Setting')->create(['tec_key' => 'email', 'tec_value' => 'noreply@tecdiary.com']);
        factory('App\Setting')->create(['tec_key' => 'phone', 'tec_value' => '0101234567']);

        echo "Creating Users\n";
        $super_role    = Role::create(['name' => 'super']);
        $admin_role    = Role::create(['name' => 'admin']);
        $staff_role    = Role::create(['name' => 'staff']);
        $customer_role = Role::create(['name' => 'customer']);
        $supplier_role = Role::create(['name' => 'supplier']);

        $super = factory('App\User')->create([
            'employee'        => 1,
            'username'        => 'super',
            'name'            => 'Super Admin',
            'email'           => 'super@tecdiary.com',
            'password'        => bcrypt('123456'),
            'can_impersonate' => 0,
        ]);
        $super->assignRole($super_role);
        Auth::login($super);

        $admin = factory('App\User')->create([
            'employee'        => 1,
            'username'        => 'admin',
            'name'            => 'Admin Staff',
            'email'           => 'admin@tecdiary.com',
            'password'        => bcrypt('123456'),
            'can_impersonate' => 0,
        ]);
        $admin->assignRole($admin_role);

        $staff = factory('App\User')->create([
            'employee' => 1,
            'username' => 'staff',
            'name'     => 'Sales Staff',
            'email'    => 'staff@tecdiary.com',
            'password' => bcrypt('123456'),
        ]);
        $staff->assignRole($staff_role);

        factory('App\User', 3)->create(['employee' => 1])->each(function ($u) {
            $u->assignRole('staff');
        });

        if (class_exists('\Modules\MPS\Database\Seeders\MPSDatabaseSeeder')) {
            $this->call(\Modules\MPS\Database\Seeders\MPSDatabaseSeeder::class);
        }
        if (class_exists('\Modules\Shop\Database\Seeders\ShopDatabaseSeeder')) {
            $this->call(\Modules\Shop\Database\Seeders\ShopDatabaseSeeder::class);
        }
    }
}
