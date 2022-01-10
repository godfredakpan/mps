<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatepurchaseItemRelationTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('purchase_item_serial');
        Schema::dropIfExists('purchase_item_variation');
    }

    public function up()
    {
        Schema::create('purchase_item_serial', function (Blueprint $table) {
            $table->uuid('serial_id');
            $table->uuid('purchase_item_id');

            $table->index('serial_id');
            $table->index('purchase_item_id');
            $table->foreign('serial_id')->references('id')->on('serials')->onDelete('cascade');
            $table->foreign('purchase_item_id')->references('id')->on('purchase_items')->onDelete('cascade');
        });

        Schema::create('purchase_item_variation', function (Blueprint $table) {
            $table->uuid('variation_id');
            $table->uuid('purchase_item_id');
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
            $table->index('purchase_item_id');
            $table->foreign('unit_id')->references('id')->on('units');
            $table->foreign('purchase_item_id')->references('id')->on('purchase_items')->onDelete('cascade');
            $table->foreign('variation_id')->references('id')->on('variations')->onDelete('cascade');
        });
    }
}
