<?php

namespace Modules\MPS\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class MPSDatabaseSeeder extends Seeder
{
    public function run()
    {
        Model::unguard();
        $this->call(MyDatabaseSeeder::class);
        $this->call(SalesTableSeeder::class);
        $this->call(PurchasesTableSeeder::class);
        $this->call(PermissionsTableSeeder::class);
    }
}
