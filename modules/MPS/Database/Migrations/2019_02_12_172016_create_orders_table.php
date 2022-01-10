<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('orders');
    }

    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('oId', 55);
            $table->date('date')->nullable();
            $table->string('reference', 55)->nullable();
            $table->string('discount', 20)->nullable();
            $table->decimal('discount_amount', 15, 4)->default('0')->nullable();
            $table->string('taxes', 1000)->nullable();
            $table->integer('total_items')->nullable();
            $table->decimal('total_quantity', 15, 4)->nullable();
            $table->decimal('grand_total', 25, 4)->nullable();
            $table->longText('items')->nullable();
            $table->text('details')->nullable();
            $table->uuid('customer_id')->nullable();
            $table->uuid('location_id')->nullable();
            $table->uuid('user_id')->nullable();
            $table->uuid('created_by')->nullable();
            $table->schemalessAttributes('extra_attributes');
            $table->timestamps();

            $table->index('user_id');
            $table->index('customer_id');
            $table->index('location_id');
            $table->unique(['oId', 'user_id']);
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('location_id')->references('id')->on('locations');
            $table->foreign('customer_id')->references('id')->on('customers');
        });
    }
}
