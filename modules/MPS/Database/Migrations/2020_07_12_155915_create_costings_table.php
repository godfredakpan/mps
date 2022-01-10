<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCostingsTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('costings');
    }

    public function up()
    {
        Schema::create('costings', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->uuid('item_id');
            $table->decimal('cost', 25, 4);
            $table->decimal('price', 25, 4);
            $table->decimal('quantity', 15, 4);
            $table->decimal('net_cost', 25, 4);
            $table->decimal('net_price', 25, 4);
            $table->uuid('sale_id');
            $table->uuid('purchase_id');
            $table->uuid('sale_item_id');
            $table->uuid('purchase_item_id');
            $table->uuid('variation_id')->nullable();
            $table->uuid('unit_id')->nullable();
            // $table->uuid('modifier_id')->nullable();
            $table->timestamps();

            $table->index('item_id');
            $table->index(['sale_id', 'sale_item_id']);
            $table->index(['purchase_id', 'purchase_item_id']);
            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('unit_id')->references('id')->on('units');
            $table->foreign('purchase_id')->references('id')->on('purchases');
            $table->foreign('purchase_item_id')->references('id')->on('purchase_items');
            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
            $table->foreign('sale_item_id')->references('id')->on('sale_items')->onDelete('cascade');
            // $table->foreign('modifier_id')->references('id')->on('modifiers')->onDelete('cascade');
        });

        Schema::create('over_sellings', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->uuid('item_id');
            $table->uuid('sale_id');
            $table->uuid('sale_item_id');
            $table->decimal('quantity', 15, 4);
            $table->decimal('price', 25, 4);
            $table->decimal('net_price', 25, 4);
            $table->uuid('variation_id')->nullable();
            // $table->uuid('modifier_id')->nullable();
            $table->timestamps();

            $table->index('item_id');
            $table->index(['sale_id', 'sale_item_id']);
            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
            // $table->foreign('sale_item_id')->references('id')->on('sale_items');
            // $table->foreign('modifier_id')->references('id')->on('modifiers')->onDelete('cascade');
        });
    }
}
