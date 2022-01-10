<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSerialsTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('serials');
    }

    public function up()
    {
        Schema::create('serials', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->uuid('item_id');
            $table->string('number');
            $table->boolean('sold')->nullable();
            $table->uuid('sale_id')->nullable();
            $table->uuid('sale_item_id')->nullable();
            $table->uuid('purchase_id')->nullable();
            $table->uuid('purchase_item_id')->nullable();

            $table->index('item_id');
            $table->index(['sale_id', 'sale_item_id']);
            $table->index(['purchase_id', 'purchase_item_id']);
            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('sale_id')->references('id')->on('sales');
            $table->foreign('purchase_id')->references('id')->on('purchases');
            $table->foreign('sale_item_id')->references('id')->on('sale_items');
            $table->foreign('purchase_item_id')->references('id')->on('purchase_items');
        });
    }
}
