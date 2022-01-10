<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UnifyAddressesToAllTables extends Migration
{
    public function down()
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn('house_no', 'street_no', 'city', 'postal_code');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('house_no', 'street_no', 'city', 'postal_code');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('house_no', 'street_no', 'city', 'postal_code');
        });
    }

    public function up()
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->string('house_no')->nullable();
            $table->string('street_no')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->string('house_no')->nullable();
            $table->string('street_no')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('house_no')->nullable();
            $table->string('street_no')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
        });
    }
}
