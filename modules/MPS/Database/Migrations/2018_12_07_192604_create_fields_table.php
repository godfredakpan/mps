<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFieldsTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('fields');
    }

    public function up()
    {
        Schema::create('fields', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('name');
            $table->string('slug');
            $table->string('type');
            $table->string('entities');
            $table->string('order')->nullable();
            $table->string('options')->nullable();
            $table->boolean('required')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();

            $table->unique('slug');
        });
    }
}
