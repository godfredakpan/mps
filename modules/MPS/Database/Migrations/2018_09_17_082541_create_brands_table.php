<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBrandsTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('brands');
    }

    public function up()
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('name')->unique();
            $table->string('slug');
            $table->string('code')->unique()->nullable();
            $table->integer('order')->nullable();
            $table->string('details')->nullable();
            $table->timestamps();
        });
    }
}
