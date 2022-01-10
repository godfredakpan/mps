<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUnitsTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('units');
    }

    public function up()
    {
        Schema::create('units', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('code');
            $table->string('name');
            $table->uuid('base_id')->nullable();
            $table->char('operator', 1)->nullable();
            $table->integer('operation_value')->nullable();
            $table->timestamps();

            $table->unique('code');
            $table->index('base_id');
            $table->foreign('base_id')->references('id')->on('units')->onDelete('cascade');
        });
    }
}
