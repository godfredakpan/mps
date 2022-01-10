<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRegistersTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('registers');
    }

    public function up()
    {
        Schema::create('registers', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('code');
            $table->string('name');
            $table->uuid('location_id');
            $table->boolean('opened')->default('0');
            $table->string('device_id')->nullable();
            $table->string('terminal_id')->nullable();
            $table->timestamps();

            $table->index('location_id');
            $table->foreign('location_id')->references('id')->on('locations');
        });
    }
}
