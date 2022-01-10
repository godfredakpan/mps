<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSaleItemRelationTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('sale_item_serial');
        Schema::dropIfExists('portion_sale_item');
        Schema::dropIfExists('sale_item_variation');
        Schema::dropIfExists('modifier_option_sale_item');
    }

    public function up()
    {
        Schema::create('sale_item_serial', function (Blueprint $table) {
            $table->uuid('serial_id');
            $table->uuid('sale_item_id');

            $table->index('serial_id');
            $table->index('sale_item_id');
            $table->foreign('sale_item_id')->references('id')->on('sale_items')->onDelete('cascade');
            $table->foreign('serial_id')->references('id')->on('serials')->onDelete('cascade');
        });

        Schema::create('sale_item_variation', function (Blueprint $table) {
            $table->uuid('sale_item_id');
            $table->uuid('variation_id');
            $table->decimal('cost', 25, 4)->nullable();
            $table->decimal('price', 25, 4)->nullable();
            $table->decimal('net_cost', 25, 4)->nullable();
            $table->decimal('net_price', 25, 4)->nullable();
            $table->decimal('quantity', 25, 4)->nullable();
            $table->uuid('unit_id', 25, 4)->nullable();
            $table->decimal('tax_amount', 25, 4)->nullable();
            $table->decimal('discount_amount', 25, 4)->nullable();
            $table->decimal('total_tax_amount', 25, 4)->nullable();
            $table->decimal('total_discount_amount', 25, 4)->nullable();
            $table->decimal('unit_price', 25, 4)->nullable();
            $table->decimal('total', 25, 4)->nullable();

            $table->index('sale_item_id');
            $table->index('variation_id');
            $table->foreign('unit_id')->references('id')->on('units');
            $table->foreign('sale_item_id')->references('id')->on('sale_items')->onDelete('cascade');
            $table->foreign('variation_id')->references('id')->on('variations')->onDelete('cascade');
        });

        Schema::create('portion_sale_item', function (Blueprint $table) {
            $table->uuid('portion_id');
            $table->uuid('sale_item_id');
            $table->decimal('cost', 25, 4)->nullable();
            $table->decimal('price', 25, 4)->nullable();
            $table->decimal('net_cost', 25, 4)->nullable();
            $table->decimal('net_price', 25, 4)->nullable();
            $table->decimal('quantity', 25, 4)->nullable();
            $table->uuid('unit_id', 25, 4)->nullable();
            $table->decimal('tax_amount', 25, 4)->nullable();
            $table->decimal('discount_amount', 25, 4)->nullable();
            $table->decimal('total_tax_amount', 25, 4)->nullable();
            $table->decimal('total_discount_amount', 25, 4)->nullable();
            $table->decimal('unit_price', 25, 4)->nullable();
            $table->decimal('total', 25, 4)->nullable();
            $table->text('choosables')->nullable();
            $table->text('essentials')->nullable();
            $table->text('portion_items')->nullable();
            $table->text('portionItems')->nullable();

            $table->index('portion_id');
            $table->index('sale_item_id');
            $table->foreign('unit_id')->references('id')->on('units');
            $table->foreign('portion_id')->references('id')->on('portions')->onDelete('cascade');
            $table->foreign('sale_item_id')->references('id')->on('sale_items')->onDelete('cascade');
        });

        Schema::create('modifier_option_sale_item', function (Blueprint $table) {
            $table->uuid('modifier_id');
            $table->uuid('sale_item_id');
            $table->uuid('modifier_option_id');
            $table->decimal('cost', 25, 4)->nullable();
            $table->decimal('price', 25, 4)->nullable();
            $table->decimal('net_cost', 25, 4)->nullable();
            $table->decimal('net_price', 25, 4)->nullable();
            $table->decimal('quantity', 25, 4)->nullable();
            $table->uuid('unit_id', 25, 4)->nullable();
            $table->decimal('tax_amount', 25, 4)->nullable();
            $table->decimal('discount_amount', 25, 4)->nullable();
            $table->decimal('total_tax_amount', 25, 4)->nullable();
            $table->decimal('total_discount_amount', 25, 4)->nullable();
            $table->decimal('unit_price', 25, 4)->nullable();
            $table->decimal('total', 25, 4)->nullable();

            $table->index('sale_item_id');
            $table->index('modifier_option_id');
            $table->foreign('unit_id')->references('id')->on('units');
            $table->foreign('modifier_id')->references('id')->on('modifiers')->onDelete('cascade');
            $table->foreign('sale_item_id')->references('id')->on('sale_items')->onDelete('cascade');
            $table->foreign('modifier_option_id')->references('id')->on('modifier_options')->onDelete('cascade');
        });
    }
}
