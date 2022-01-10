<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStockTrailsTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('stock_trails');
    }

    public function up()
    {
        Schema::create('stock_trails', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->uuid('item_id');
            $table->string('memo')->nullable();
            $table->decimal('quantity', 25, 4);
            $table->string('type')->nullable();
            $table->uuid('unit_id')->nullable();
            $table->nullableUuidMorphs('subject');
            $table->uuid('portion_id')->nullable();
            $table->uuid('location_id')->nullable();
            $table->uuid('modifier_id')->nullable();
            $table->uuid('variation_id')->nullable();
            $table->timestamps();

            $table->index('item_id');
            $table->index('portion_id');
            $table->index('location_id');
            $table->index('modifier_id');
            $table->index('variation_id');
            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('unit_id')->references('id')->on('units');
            $table->foreign('portion_id')->references('id')->on('portions');
            $table->foreign('location_id')->references('id')->on('locations');
            $table->foreign('modifier_id')->references('id')->on('modifiers');
            $table->foreign('variation_id')->references('id')->on('variations');
        });
    }
}
