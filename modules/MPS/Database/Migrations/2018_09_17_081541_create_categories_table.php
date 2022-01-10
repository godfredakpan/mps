<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategoriesTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('categories');
        Schema::dropIfExists('categorizables');
    }

    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('code');
            $table->string('name');
            $table->string('slug');
            $table->integer('order')->nullable();
            $table->uuid('parent_id')->nullable();
            $table->timestamps();

            $table->unique('code');
            $table->unique('slug');
            $table->index('parent_id');
            $table->foreign('parent_id')->references('id')->on('categories')->onDelete('cascade');
        });

        Schema::create('categorizables', function (Blueprint $table) {
            $table->uuid('category_id');
            $table->uuid('categorizable_id');
            $table->string('categorizable_type');

            $table->index('category_id');
            $table->index(['categorizable_id', 'categorizable_type']);
        });
    }
}
