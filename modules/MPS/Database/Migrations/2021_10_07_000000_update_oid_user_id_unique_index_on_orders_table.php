<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateOidUserIdUniqueIndexOnOrdersTable extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['oId', 'user_id']);
            $table->unique(['oId', 'user_id', 'location_id']);
        });
    }
}
