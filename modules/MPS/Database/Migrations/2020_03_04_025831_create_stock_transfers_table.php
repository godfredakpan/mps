<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStockTransfersTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('stock_transfer_item_variation');
        Schema::dropIfExists('serial_stock_transfer_item');
        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('stock_transfers');
    }

    public function up()
    {
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('reference');
            $table->uuid('to')->index();
            $table->uuid('from')->index();
            $table->uuid('user_id')->index();
            $table->text('details')->nullable();
            $table->string('status')->nullable();
            $table->schemalessAttributes('extra_attributes');
            $table->unsignedInteger('number')->index();
            $table->timestamps();

            $table->foreign('to')->references('id')->on('locations');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('from')->references('id')->on('locations');
        });

        Schema::create('stock_transfer_items', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('code');
            $table->string('name');
            $table->uuid('item_id');
            $table->decimal('quantity', 15, 4);
            $table->uuid('unit_id')->nullable();
            $table->uuid('stock_transfer_id')->index();
            $table->string('batch_no')->nullable();
            $table->date('expiry_date')->nullable();
            $table->timestamps();

            $table->index('item_id');
            $table->index('unit_id');
            $table->index(['stock_transfer_id', 'item_id']);
            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('unit_id')->references('id')->on('units');
            $table->foreign('stock_transfer_id')->references('id')->on('stock_transfers')->onDelete('cascade');
        });

        Schema::create('serial_stock_transfer_item', function (Blueprint $table) {
            $table->uuid('serial_id');
            $table->uuid('stock_transfer_item_id');

            $table->index('serial_id');
            $table->index('stock_transfer_item_id');
            $table->foreign('serial_id')->references('id')->on('serials')->onDelete('cascade');
            $table->foreign('stock_transfer_item_id')->references('id')->on('stock_transfer_items')->onDelete('cascade');
        });

        Schema::create('stock_transfer_item_variation', function (Blueprint $table) {
            $table->uuid('variation_id');
            $table->uuid('stock_transfer_item_id');
            $table->decimal('quantity', 15, 4)->nullable();

            $table->index('variation_id');
            $table->index('stock_transfer_item_id');
            $table->foreign('variation_id')->references('id')->on('variations')->onDelete('cascade');
            $table->foreign('stock_transfer_item_id')->references('id')->on('stock_transfer_items')->onDelete('cascade');
        });
    }
}
