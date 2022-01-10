<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStockAdjustmentsTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('stock_adjustment_item_variation');
        Schema::dropIfExists('serial_stock_adjustment_item');
        Schema::dropIfExists('stock_adjustment_items');
        Schema::dropIfExists('stock_adjustments');
    }

    public function up()
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->date('date');
            $table->string('type');
            $table->string('reference');
            $table->string('discount', 20)->nullable();
            $table->decimal('discount_amount', 15, 4)->default('0')->nullable();
            $table->decimal('total_tax_amount', 15, 4)->default('0')->nullable();
            $table->decimal('order_tax_amount', 15, 4)->default('0')->nullable();
            $table->decimal('item_tax_amount', 15, 4)->default('0')->nullable();
            $table->decimal('recoverable_tax_amount', 15, 4)->default('0')->nullable();
            $table->decimal('recoverable_tax_calculated_on', 15, 4)->default('0')->nullable();
            $table->decimal('total', 25, 4);
            $table->decimal('grand_total', 25, 4);
            $table->uuid('user_id')->index();
            $table->text('details')->nullable();
            $table->string('status')->nullable();
            $table->boolean('draft')->nullable();
            $table->uuid('location_id')->index();
            $table->schemalessAttributes('extra_attributes');
            $table->unsignedInteger('number')->index();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('location_id')->references('id')->on('locations');
        });

        Schema::create('stock_adjustment_items', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('code');
            $table->string('name');
            $table->decimal('cost', 25, 4);
            $table->decimal('quantity', 15, 4);
            $table->decimal('net_cost', 25, 4);
            $table->decimal('unit_cost', 25, 4);
            $table->decimal('subtotal', 25, 4);
            $table->decimal('base_net_cost', 25, 4);
            $table->decimal('base_unit_cost', 25, 4);
            $table->uuid('stock_adjustment_id')->index();
            $table->uuid('item_id')->nullable();
            $table->uuid('unit_id')->nullable();
            $table->string('batch_no')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('expired_quantity', 15, 4)->nullable();
            $table->string('item_taxes')->nullable();
            $table->decimal('tax_amount', 15, 4)->nullable();
            $table->decimal('total_tax_amount', 15, 4)->nullable();
            $table->string('discount')->nullable();
            $table->decimal('discount_amount', 15, 4)->nullable();
            $table->decimal('total_discount_amount', 15, 4)->nullable();
            $table->uuid('location_id')->nullable();
            $table->timestamps();

            $table->index('item_id');
            $table->index('unit_id');
            $table->index(['stock_adjustment_id', 'item_id']);
            $table->foreign('unit_id')->references('id')->on('units');
            $table->foreign('location_id')->references('id')->on('locations');
            $table->foreign('stock_adjustment_id')->references('id')->on('stock_adjustments')->onDelete('cascade');
        });

        Schema::create('serial_stock_adjustment_item', function (Blueprint $table) {
            $table->uuid('serial_id');
            $table->uuid('stock_adjustment_item_id');

            $table->index('serial_id');
            $table->index('stock_adjustment_item_id');
            $table->foreign('serial_id')->references('id')->on('serials')->onDelete('cascade');
            $table->foreign('stock_adjustment_item_id')->references('id')->on('stock_adjustment_items')->onDelete('cascade');
        });

        Schema::create('stock_adjustment_item_variation', function (Blueprint $table) {
            $table->uuid('variation_id');
            $table->uuid('stock_adjustment_item_id');
            $table->decimal('cost', 25, 4)->nullable();
            $table->decimal('net_cost', 25, 4)->nullable();
            $table->decimal('quantity', 25, 4)->nullable();
            $table->uuid('unit_id')->nullable();
            $table->decimal('tax_amount', 25, 4)->nullable();
            $table->decimal('discount_amount', 25, 4)->nullable();
            $table->decimal('total_tax_amount', 25, 4)->nullable();
            $table->decimal('total_discount_amount', 25, 4)->nullable();
            $table->decimal('unit_cost', 25, 4)->nullable();
            $table->decimal('total', 25, 4)->nullable();

            $table->index('variation_id');
            $table->index('stock_adjustment_item_id');
            $table->foreign('unit_id')->references('id')->on('units');
            $table->foreign('variation_id')->references('id')->on('variations')->onDelete('cascade');
            $table->foreign('stock_adjustment_item_id')->references('id')->on('stock_adjustment_items')->onDelete('cascade');
        });
    }
}
