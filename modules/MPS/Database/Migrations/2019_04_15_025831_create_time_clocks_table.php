<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTimeClocksTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('time_clocks');
    }

    public function up()
    {
        Schema::create('time_clocks', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->uuid('user_id');
            $table->decimal('rate', 10, 4);
            $table->dateTime('in_time');
            $table->dateTime('out_time')->nullable();
            $table->text('details')->nullable();
            $table->uuid('location_id');
            $table->timestamps();

            $table->index('user_id');
            $table->index('location_id');
            $table->foreign('location_id')->references('id')->on('locations');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
}
