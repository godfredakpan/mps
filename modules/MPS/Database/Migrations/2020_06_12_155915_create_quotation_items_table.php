<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuotationItemsTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('modifier_option_quotation_item');
        Schema::dropIfExists('portion_quotation_item');
        Schema::dropIfExists('quotation_item_variation');
        Schema::dropIfExists('quotation_item_serial');
        Schema::dropIfExists('promotion_quotation_item');
        Schema::dropIfExists('quotation_items');
    }

    public function up()
    {
        Schema::create('quotation_items', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('code');
            $table->string('name');
            $table->decimal('price', 25, 4);
            $table->decimal('quantity', 15, 4);
            $table->decimal('net_price', 25, 4);
            $table->decimal('unit_price', 25, 4);
            $table->decimal('subtotal', 25, 4);
            $table->uuid('quotation_id')->index();
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
            $table->index(['quotation_id', 'item_id']);
            $table->foreign('unit_id')->references('id')->on('units');
            $table->foreign('quotation_id')->references('id')->on('quotations')->onDelete('cascade');
        });

        Schema::create('promotion_quotation_item', function (Blueprint $table) {
            $table->uuid('promotion_id')->index();
            $table->uuid('quotation_item_id')->index();
            $table->index(['promotion_id', 'quotation_item_id']);
            $table->unique(['promotion_id', 'quotation_item_id']);
            $table->foreign('promotion_id')->references('id')->on('promotions');
            $table->foreign('quotation_item_id')->references('id')->on('quotation_items')->onDelete('cascade');
        });

        Schema::create('quotation_item_serial', function (Blueprint $table) {
            $table->uuid('serial_id');
            $table->uuid('quotation_item_id');

            $table->index('serial_id');
            $table->index('quotation_item_id');
            $table->foreign('quotation_item_id')->references('id')->on('quotation_items')->onDelete('cascade');
            $table->foreign('serial_id')->references('id')->on('serials')->onDelete('cascade');
        });

        Schema::create('quotation_item_variation', function (Blueprint $table) {
            $table->uuid('variation_id');
            $table->uuid('quotation_item_id');
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
            $table->index('quotation_item_id');
            $table->foreign('unit_id')->references('id')->on('units');
            $table->foreign('quotation_item_id')->references('id')->on('quotation_items')->onDelete('cascade');
            $table->foreign('variation_id')->references('id')->on('variations')->onDelete('cascade');
        });

        Schema::create('portion_quotation_item', function (Blueprint $table) {
            $table->uuid('portion_id');
            $table->uuid('quotation_item_id');
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
            $table->text('choosables')->nullable();
            $table->text('essentials')->nullable();
            $table->text('portion_items')->nullable();
            $table->text('portionItems')->nullable();

            $table->index('portion_id');
            $table->index('quotation_item_id');
            $table->foreign('unit_id')->references('id')->on('units');
            $table->foreign('portion_id')->references('id')->on('portions')->onDelete('cascade');
            $table->foreign('quotation_item_id')->references('id')->on('quotation_items')->onDelete('cascade');
        });

        Schema::create('modifier_option_quotation_item', function (Blueprint $table) {
            $table->uuid('modifier_id');
            $table->uuid('quotation_item_id');
            $table->uuid('modifier_option_id');
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

            $table->index('quotation_item_id');
            $table->index('modifier_option_id');
            $table->foreign('unit_id')->references('id')->on('units');
            $table->foreign('modifier_id')->references('id')->on('modifiers')->onDelete('cascade');
            $table->foreign('quotation_item_id')->references('id')->on('quotation_items')->onDelete('cascade');
            $table->foreign('modifier_option_id')->references('id')->on('modifier_options')->onDelete('cascade');
        });
    }
}
