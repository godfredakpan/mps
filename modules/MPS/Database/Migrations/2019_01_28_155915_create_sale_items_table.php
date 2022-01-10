<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSaleItemsTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('sale_item_promotion');
        Schema::dropIfExists('sale_items');
    }

    public function up()
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('code');
            $table->string('name');
            $table->decimal('price', 25, 4);
            $table->decimal('quantity', 15, 4);
            $table->decimal('net_price', 25, 4);
            $table->decimal('unit_price', 25, 4);
            $table->decimal('subtotal', 25, 4);
            $table->uuid('sale_id')->index();
            $table->uuid('item_id')->nullable();
            $table->uuid('unit_id')->nullable();
            $table->string('batch_no')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('item_taxes')->nullable();
            $table->string('item_promotions')->nullable();
            $table->decimal('tax_amount', 15, 4)->nullable();
            $table->decimal('total_tax_amount', 15, 4)->nullable();
            $table->string('discount')->nullable();
            $table->decimal('discount_amount', 15, 4)->nullable();
            $table->decimal('total_discount_amount', 15, 4)->nullable();
            $table->tinyInteger('order')->nullable();
            $table->timestamps();

            $table->index('item_id');
            $table->index('unit_id');
            $table->index(['sale_id', 'item_id']);
            $table->foreign('unit_id')->references('id')->on('units');
            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
        });

        Schema::create('promotion_sale_item', function (Blueprint $table) {
            $table->uuid('promotion_id')->index();
            $table->uuid('sale_item_id')->index();
            $table->index(['promotion_id', 'sale_item_id']);
            $table->unique(['promotion_id', 'sale_item_id']);
            $table->foreign('promotion_id')->references('id')->on('promotions');
            $table->foreign('sale_item_id')->references('id')->on('sale_items')->onDelete('cascade');
        });
    }
}
