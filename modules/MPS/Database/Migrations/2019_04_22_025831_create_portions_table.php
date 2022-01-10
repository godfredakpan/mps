<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePortionsTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('portion_items');
        Schema::dropIfExists('portions');
    }

    public function up()
    {
        Schema::create('portions', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('sku');
            $table->string('name');
            $table->uuid('item_id');
            $table->decimal('cost', 25, 4)->nullable();
            $table->decimal('price', 25, 4)->nullable();
            $table->timestamps();

            $table->index('sku');
            $table->unique('sku');
            $table->index('item_id');
            $table->unique(['item_id', 'name']);
            $table->foreign('item_id')->references('id')->on('items');
        });

        Schema::create('portion_items', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->uuid('item_id');
            $table->uuid('portion_id');
            $table->uuid('variation_id')->nullable();
            $table->decimal('quantity', 25, 4)->nullable();

            $table->index('item_id');
            $table->index('portion_id');
            $table->unique(['portion_id', 'item_id']);
            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('variation_id')->references('id')->on('variations');
            $table->foreign('portion_id')->references('id')->on('portions')->onDelete('cascade');
        });
    }
}
