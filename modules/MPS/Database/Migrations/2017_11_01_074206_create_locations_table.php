<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocationsTable extends Migration
{
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['location_id']);
            $table->dropColumn('location_id');
        });
        Schema::dropIfExists('location_user');
        Schema::dropIfExists('account_location');
        Schema::dropIfExists('locations');
    }

    public function up()
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('name');
            $table->string('phone');
            $table->string('email');
            $table->string('address');
            $table->uuid('account_id');
            $table->string('logo')->nullable();
            $table->string('state')->nullable();
            $table->string('header')->nullable();
            $table->string('footer')->nullable();
            $table->string('country')->nullable();
            $table->string('color', 10)->nullable();
            $table->string('state_name')->nullable();
            $table->string('country_name')->nullable();
            $table->string('timezone', 55)->nullable();
            $table->schemalessAttributes('extra_attributes');
            $table->timestamps();

            $table->index('account_id');
            $table->foreign('account_id')->references('id')->on('accounts');
        });

        Schema::create('account_location', function (Blueprint $table) {
            $table->uuid('account_id')->index();
            $table->uuid('location_id')->index();
            $table->index(['account_id', 'location_id']);
            $table->unique(['account_id', 'location_id']);
            $table->foreign('account_id')->references('id')->on('accounts');
            $table->foreign('location_id')->references('id')->on('locations');
        });

        Schema::create('location_user', function (Blueprint $table) {
            $table->uuid('location_id')->index();
            $table->uuid('user_id')->index();
            $table->index(['location_id', 'user_id']);
            $table->unique(['location_id', 'user_id']);
            $table->foreign('location_id')->references('id')->on('locations');
            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->uuid('location_id')->nullable()->after('can_impersonate');
            $table->index('location_id');
            $table->foreign('location_id')->references('id')->on('locations');
        });
    }
}
