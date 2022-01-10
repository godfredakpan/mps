<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecurringSaleItemsTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('recurring_sale_item_variation');
        Schema::dropIfExists('recurring_sale_item_promotion');
        Schema::dropIfExists('recurring_sale_items');
    }

    public function up()
    {
        Schema::create('recurring_sale_items', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('code');
            $table->string('name');
            $table->decimal('price', 25, 4);
            $table->decimal('quantity', 15, 4);
            $table->decimal('net_price', 25, 4);
            $table->decimal('unit_price', 25, 4);
            $table->decimal('subtotal', 25, 4);
            $table->uuid('item_id')->nullable();
            $table->uuid('unit_id')->nullable();
            $table->string('batch_no')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('item_taxes')->nullable();
            $table->uuid('recurring_sale_id')->index();
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
            $table->index(['recurring_sale_id', 'item_id']);
            $table->foreign('unit_id')->references('id')->on('units');
            $table->foreign('recurring_sale_id')->references('id')->on('recurring_sales')->onDelete('cascade');
        });

        Schema::create('promotion_recurring_sale_item', function (Blueprint $table) {
            $table->uuid('promotion_id')->index();
            $table->uuid('recurring_sale_item_id')->index();
            $table->foreign('promotion_id')->references('id')->on('promotions');
            $table->foreign('recurring_sale_item_id')->references('id')->on('recurring_sale_items')->onDelete('cascade');
            $table->index(['promotion_id', 'recurring_sale_item_id'], 'rsip_promotion_id_recurring_sale_item_id_index');
            $table->unique(['promotion_id', 'recurring_sale_item_id'], 'rsip_promotion_id_recurring_sale_item_id_unique');
        });

        Schema::create('recurring_sale_item_variation', function (Blueprint $table) {
            $table->uuid('variation_id');
            $table->uuid('recurring_sale_item_id');
            $table->decimal('cost', 25, 4)->nullable();
            $table->decimal('price', 25, 4)->nullable();
            $table->decimal('net_cost', 25, 4)->nullable();
            $table->decimal('net_price', 25, 4)->nullable();
            $table->decimal('quantity', 25, 4)->nullable();
            $table->uuid('unit_id')->nullable();
            $table->decimal('tax_amount', 25, 4)->nullable();
            $table->decimal('discount_amount', 25, 4)->nullable();
            $table->decimal('total_tax_amount', 25, 4)->nullable();
            $table->decimal('total_discount_amount', 25, 4)->nullable();
            $table->decimal('unit_price', 25, 4)->nullable();
            $table->decimal('total', 25, 4)->nullable();

            $table->index('variation_id');
            $table->index('recurring_sale_item_id');
            $table->foreign('unit_id')->references('id')->on('units');
            $table->foreign('variation_id')->references('id')->on('variations');
            $table->foreign('recurring_sale_item_id')->references('id')->on('recurring_sale_items');
        });

        Schema::create('portion_recurring_sale_item', function (Blueprint $table) {
            $table->uuid('portion_id');
            $table->uuid('recurring_sale_item_id');
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
            $table->index('recurring_sale_item_id');
            $table->foreign('unit_id')->references('id')->on('units');
            $table->foreign('portion_id')->references('id')->on('portions')->onDelete('cascade');
            $table->foreign('recurring_sale_item_id')->references('id')->on('recurring_sale_items')->onDelete('cascade');
        });

        Schema::create('modifier_option_recurring_sale_item', function (Blueprint $table) {
            $table->uuid('modifier_id');
            $table->uuid('recurring_sale_item_id');
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

            $table->index('modifier_option_id');
            $table->index('recurring_sale_item_id');
            $table->foreign('unit_id')->references('id')->on('units');
            $table->foreign('modifier_id')->references('id')->on('modifiers')->onDelete('cascade');
            $table->foreign('modifier_option_id', 'morsi_modifier_option_id_foreign')->references('id')->on('modifier_options')->onDelete('cascade');
            $table->foreign('recurring_sale_item_id', 'morsi_recurring_sale_item_id_foreign')->references('id')->on('recurring_sale_items')->onDelete('cascade');
        });
    }
}
