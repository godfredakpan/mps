<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHallsTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('hall_tables');
        Schema::dropIfExists('halls');
    }

    public function up()
    {
        Schema::create('halls', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('code');
            $table->string('title');
            $table->string('details')->nullable();
            $table->uuid('location_id')->nullable();
            $table->schemalessAttributes('extra_attributes');
            $table->timestamps();

            $table->unique('code');
            $table->index('location_id');
            $table->foreign('location_id')->references('id')->on('locations');
        });

        Schema::create('hall_tables', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('code');
            $table->string('title');
            $table->string('details')->nullable();
            $table->uuid('hall_id')->nullable();
            $table->schemalessAttributes('extra_attributes');
            $table->timestamps();

            $table->index('hall_id');
            $table->unique(['code', 'hall_id']);
            $table->foreign('hall_id')->references('id')->on('halls')->onDelete('cascade');
        });
    }
}
