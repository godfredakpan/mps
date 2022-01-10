<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReturnsTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('return_order_item_variation');
        Schema::dropIfExists('promotion_return_order_item');
        Schema::dropIfExists('return_order_item_serial');
        Schema::dropIfExists('return_order_items');
        Schema::dropIfExists('return_orders');
    }

    public function up()
    {
        Schema::create('return_orders', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->date('date');
            $table->string('reference');
            $table->uuid('location_id');
            $table->decimal('total', 25, 4);
            $table->decimal('grand_total', 25, 4);
            $table->enum('type', ['sale', 'purchase']);
            $table->string('discount', 20)->nullable();
            $table->decimal('discount_amount', 15, 4)->default('0')->nullable();
            $table->decimal('total_tax_amount', 15, 4)->default('0')->nullable();
            $table->decimal('order_tax_amount', 15, 4)->default('0')->nullable();
            $table->decimal('item_tax_amount', 15, 4)->default('0')->nullable();
            $table->decimal('recoverable_tax_amount', 15, 4)->default('0')->nullable();
            $table->decimal('recoverable_tax_calculated_on', 15, 4)->default('0')->nullable();
            $table->text('details')->nullable();
            $table->string('hash')->nullable();
            $table->uuid('user_id')->nullable();
            $table->uuid('customer_id')->nullable();
            $table->uuid('supplier_id')->nullable();
            $table->uuid('transaction_id')->nullable();
            $table->decimal('shipping', 15, 4)->nullable();
            $table->boolean('deduct_from_register')->default('0')->nullable();
            $table->schemalessAttributes('extra_attributes');
            $table->unsignedInteger('number')->index();
            $table->timestamps();

            $table->index('type');
            $table->index('hash');
            $table->index('user_id');
            $table->index('reference');
            $table->index('customer_id');
            $table->index('location_id');
            $table->index('supplier_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->foreign('location_id')->references('id')->on('locations');
            $table->foreign('supplier_id')->references('id')->on('suppliers');
        });

        Schema::create('return_order_items', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('code');
            $table->string('name');
            $table->decimal('quantity', 15, 4);
            $table->decimal('cost', 25, 4)->nullable();
            $table->decimal('price', 25, 4)->nullable();
            $table->decimal('net_cost', 25, 4)->nullable();
            $table->decimal('unit_cost', 25, 4)->nullable();
            $table->decimal('net_price', 25, 4)->nullable();
            $table->decimal('unit_price', 25, 4)->nullable();
            $table->decimal('subtotal', 25, 4);
            $table->uuid('return_order_id')->index();
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
            $table->index(['return_order_id', 'item_id']);
            $table->foreign('unit_id')->references('id')->on('units');
            $table->foreign('return_order_id')->references('id')->on('return_orders')->onDelete('cascade');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->uuid('return_order_id')->nullable()->index();
            $table->foreign('return_order_id')->references('id')->on('return_orders');
        });

        Schema::create('payment_return_order', function (Blueprint $table) {
            $table->decimal('amount', 25, 4);
            $table->uuid('payment_id')->nullable();
            $table->uuid('return_order_id')->nullable();

            $table->index('payment_id');
            $table->index('return_order_id');
            $table->index(['return_order_id', 'payment_id']);
            $table->unique(['return_order_id', 'payment_id']);
            $table->foreign('payment_id')->references('id')->on('payments');
            $table->foreign('return_order_id')->references('id')->on('return_orders')->onDelete('cascade');
        });

        Schema::create('return_order_item_serial', function (Blueprint $table) {
            $table->uuid('serial_id');
            $table->uuid('return_order_item_id');

            $table->index('serial_id');
            $table->index('return_order_item_id');
            $table->foreign('serial_id')->references('id')->on('serials')->onDelete('cascade');
            $table->foreign('return_order_item_id')->references('id')->on('return_order_items')->onDelete('cascade');
        });

        Schema::create('promotion_return_order_item', function (Blueprint $table) {
            $table->uuid('promotion_id')->index();
            $table->uuid('return_order_item_id')->index();
            $table->index(['promotion_id', 'return_order_item_id'], 'p_roi_promotion_id_return_order_item_id_index');
            $table->unique(['promotion_id', 'return_order_item_id'], 'p_roi_promotion_id_return_order_item_id_unique');
            $table->foreign('promotion_id')->references('id')->on('promotions');
            $table->foreign('return_order_item_id')->references('id')->on('return_order_items')->onDelete('cascade');
        });

        Schema::create('return_order_item_variation', function (Blueprint $table) {
            $table->uuid('variation_id');
            $table->uuid('return_order_item_id');
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
            $table->decimal('unit_cost', 25, 4)->nullable();
            $table->decimal('unit_price', 25, 4)->nullable();
            $table->decimal('total', 25, 4)->nullable();

            $table->index('variation_id');
            $table->index('return_order_item_id');
            $table->foreign('unit_id')->references('id')->on('units');
            $table->foreign('variation_id')->references('id')->on('variations')->onDelete('cascade');
            $table->foreign('return_order_item_id')->references('id')->on('return_order_items')->onDelete('cascade');
        });

        Schema::create('portion_return_order_item', function (Blueprint $table) {
            $table->uuid('portion_id');
            $table->uuid('return_order_item_id');
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
            $table->decimal('unit_cost', 25, 4)->nullable();
            $table->decimal('unit_price', 25, 4)->nullable();
            $table->decimal('total', 25, 4)->nullable();
            $table->text('choosables')->nullable();
            $table->text('essentials')->nullable();
            $table->text('portion_items')->nullable();
            $table->text('portionItems')->nullable();

            $table->index('portion_id');
            $table->index('return_order_item_id');
            $table->foreign('unit_id')->references('id')->on('units');
            $table->foreign('portion_id')->references('id')->on('portions')->onDelete('cascade');
            $table->foreign('return_order_item_id')->references('id')->on('return_order_items')->onDelete('cascade');
        });

        Schema::create('modifier_option_return_order_item', function (Blueprint $table) {
            $table->uuid('modifier_id');
            $table->uuid('return_order_item_id');
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
            $table->decimal('unit_cost', 25, 4)->nullable();
            $table->decimal('unit_price', 25, 4)->nullable();
            $table->decimal('total', 25, 4)->nullable();

            $table->index('modifier_option_id');
            $table->index('return_order_item_id');
            $table->foreign('unit_id')->references('id')->on('units');
            $table->foreign('modifier_id')->references('id')->on('modifiers')->onDelete('cascade');
            $table->foreign('modifier_option_id')->references('id')->on('modifier_options')->onDelete('cascade');
            $table->foreign('return_order_item_id')->references('id')->on('return_order_items')->onDelete('cascade');
        });
    }
}
