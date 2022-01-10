<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeliveriesTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('delivery_item_variation');
        Schema::dropIfExists('serial_delivery_item');
        Schema::dropIfExists('delivery_items');
        Schema::dropIfExists('deliveries');
    }

    public function up()
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->date('date');
            $table->string('reference');
            $table->uuid('user_id')->index();
            $table->uuid('sale_id')->index();
            $table->text('details')->nullable();
            $table->string('status')->nullable();
            $table->string('driver')->nullable();
            $table->uuid('customer_id')->index();
            $table->uuid('location_id')->index();
            $table->uuid('delivered_by')->nullable();
            $table->string('received_by')->nullable();
            $table->datetime('delivered_at')->nullable();
            $table->schemalessAttributes('extra_attributes');
            $table->unsignedInteger('number')->index();
            $table->timestamps();

            $table->foreign('sale_id')->references('id')->on('sales');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('delivered_by')->references('id')->on('users');
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->foreign('location_id')->references('id')->on('locations');
        });

        Schema::create('delivery_items', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('code');
            $table->string('name');
            $table->uuid('item_id');
            $table->decimal('quantity', 15, 4);
            $table->uuid('unit_id')->nullable();
            $table->uuid('delivery_id')->index();
            $table->string('batch_no')->nullable();
            $table->date('expiry_date')->nullable();
            $table->timestamps();

            $table->index('item_id');
            $table->index('unit_id');
            $table->index(['delivery_id', 'item_id']);
            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('unit_id')->references('id')->on('units');
            $table->foreign('delivery_id')->references('id')->on('deliveries')->onDelete('cascade');
        });

        Schema::create('delivery_item_serial', function (Blueprint $table) {
            $table->uuid('serial_id');
            $table->uuid('delivery_item_id');

            $table->index('serial_id');
            $table->index('delivery_item_id');
            $table->foreign('serial_id')->references('id')->on('serials')->onDelete('cascade');
            $table->foreign('delivery_item_id')->references('id')->on('delivery_items')->onDelete('cascade');
        });

        Schema::create('delivery_item_variation', function (Blueprint $table) {
            $table->uuid('variation_id');
            $table->uuid('delivery_item_id');
            $table->decimal('quantity', 15, 4)->nullable();
            $table->uuid('unit_id', 25, 4)->nullable();

            $table->index('variation_id');
            $table->index('delivery_item_id');
            $table->foreign('unit_id')->references('id')->on('units');
            $table->foreign('variation_id')->references('id')->on('variations')->onDelete('cascade');
            $table->foreign('delivery_item_id')->references('id')->on('delivery_items')->onDelete('cascade');
        });

        Schema::create('delivery_item_portion', function (Blueprint $table) {
            $table->uuid('portion_id');
            $table->uuid('delivery_item_id');
            $table->decimal('quantity', 25, 4)->nullable();
            $table->uuid('unit_id', 25, 4)->nullable();
            $table->text('choosables')->nullable();

            $table->index('portion_id');
            $table->index('delivery_item_id');
            $table->foreign('unit_id')->references('id')->on('units');
            $table->foreign('portion_id')->references('id')->on('portions')->onDelete('cascade');
            $table->foreign('delivery_item_id')->references('id')->on('delivery_items')->onDelete('cascade');
        });

        Schema::create('delivery_item_modifier_option', function (Blueprint $table) {
            $table->uuid('modifier_id');
            $table->uuid('delivery_item_id');
            $table->uuid('modifier_option_id');
            $table->decimal('quantity', 25, 4)->nullable();
            $table->uuid('unit_id', 25, 4)->nullable();

            $table->index('delivery_item_id');
            $table->index('modifier_option_id');
            $table->foreign('unit_id')->references('id')->on('units');
            $table->foreign('modifier_id')->references('id')->on('modifiers')->onDelete('cascade');
            $table->foreign('delivery_item_id')->references('id')->on('delivery_items')->onDelete('cascade');
            $table->foreign('modifier_option_id')->references('id')->on('modifier_options')->onDelete('cascade');
        });
    }
}
