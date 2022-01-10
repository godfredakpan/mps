<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIngredientsTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('ingredient_item');
        Schema::dropIfExists('ingredients');
    }

    public function up()
    {
        Schema::create('ingredients', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('sku')->index()->unique();
            $table->string('code')->index()->unique();
            $table->string('name');
            $table->decimal('cost', 25, 4);
            $table->decimal('price', 25, 4)->nullable();
            $table->decimal('quantity', 25, 4);
            $table->string('details')->nullable();
            $table->schemalessAttributes('extra_attributes');
            $table->timestamps();
        });

        Schema::create('ingredient_item', function (Blueprint $table) {
            $table->uuid('item_id');
            $table->uuid('ingredient_id');
            $table->decimal('quantity', 25, 4)->nullable();

            $table->index('item_id');
            $table->index('ingredient_id');
            $table->unique(['item_id', 'ingredient_id']);
            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('ingredient_id')->references('id')->on('ingredients')->onDelete('cascade');
        });
    }
}
