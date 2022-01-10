<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseItemsTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('purchase_items');
    }

    public function up()
    {
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('code');
            $table->string('name');
            $table->decimal('cost', 25, 4);
            $table->decimal('quantity', 15, 4);
            $table->decimal('net_cost', 25, 4);
            $table->decimal('unit_cost', 25, 4);
            $table->decimal('subtotal', 25, 4);
            $table->decimal('balance', 15, 4);
            $table->decimal('base_net_cost', 25, 4);
            $table->decimal('base_unit_cost', 25, 4);
            $table->uuid('purchase_id')->index();
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
            $table->tinyInteger('order')->nullable();
            $table->timestamps();

            $table->index('item_id');
            $table->index('unit_id');
            $table->index(['purchase_id', 'item_id']);
            $table->foreign('unit_id')->references('id')->on('units');
            $table->foreign('location_id')->references('id')->on('locations');
            $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('cascade');
        });
    }
}
