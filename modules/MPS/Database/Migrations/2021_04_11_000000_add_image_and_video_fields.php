<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddImageAndVideoFields extends Migration
{
    public function up()
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->string('photo')->nullable()->after('order');
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->string('photo')->nullable()->after('order');
        });
        Schema::table('items', function (Blueprint $table) {
            $table->string('video')->nullable()->after('photo');
        });
    }
}
