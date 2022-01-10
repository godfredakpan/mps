<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('sales');
    }

    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->date('date');
            $table->string('type', 55);
            $table->string('reference');
            $table->string('discount', 20)->nullable();
            $table->decimal('discount_amount', 15, 4)->default('0')->nullable();
            $table->decimal('total_tax_amount', 15, 4)->default('0')->nullable();
            $table->decimal('order_tax_amount', 15, 4)->default('0')->nullable();
            $table->decimal('item_tax_amount', 15, 4)->default('0')->nullable();
            $table->decimal('recoverable_tax_amount', 15, 4)->default('0')->nullable();
            $table->decimal('recoverable_tax_calculated_on', 15, 4)->default('0')->nullable();
            $table->decimal('shipping', 15, 4)->nullable();
            $table->decimal('total', 25, 4);
            $table->decimal('grand_total', 25, 4);
            $table->text('details')->nullable();
            $table->string('hash')->nullable();
            $table->boolean('pos')->default('0')->nullable();
            $table->boolean('shop')->default('0')->nullable();
            $table->boolean('paid')->default('0')->nullable();
            $table->boolean('void')->default('0')->nullable();
            $table->boolean('draft')->default('0')->nullable();
            $table->uuid('customer_id');
            $table->uuid('location_id');
            $table->uuid('orderId')->nullable();
            $table->uuid('user_id')->nullable();
            $table->uuid('register_id')->nullable();
            $table->uuid('transaction_id')->nullable();
            $table->uuid('recurring_sale_id')->nullable();
            $table->uuid('register_record_id')->nullable();
            $table->schemalessAttributes('extra_attributes');
            $table->unsignedInteger('number')->index();
            $table->timestamp('reported_at')->nullable();
            $table->timestamps();

            $table->index('hash');
            $table->index('user_id');
            $table->index('reference');
            $table->index('customer_id');
            $table->index('location_id');
            $table->index('recurring_sale_id');
            $table->index('register_record_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->foreign('location_id')->references('id')->on('locations');
            $table->foreign('register_id')->references('id')->on('registers');
            $table->foreign('register_record_id')->references('id')->on('register_records');
        });

        // \DB::statement('ALTER Table sales add number INTEGER NOT NULL UNIQUE AUTO_INCREMENT AFTER id;');
    }
}
