<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecurringSalesTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('recurring_sales');
    }

    public function up()
    {
        Schema::create('recurring_sales', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->date('start_date');
            $table->string('reference');
            $table->string('repeat', 30);
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
            $table->integer('create_before')->nullable();
            $table->date('last_created_at')->nullable();
            $table->boolean('draft')->default('0')->nullable();
            $table->boolean('payment')->default('0')->nullable();
            $table->uuid('customer_id');
            $table->uuid('location_id');
            $table->uuid('user_id')->nullable();
            $table->schemalessAttributes('extra_attributes');
            $table->unsignedInteger('number')->index();
            $table->timestamps();

            $table->index('hash');
            $table->index('user_id');
            $table->index('reference');
            $table->index('customer_id');
            $table->index('location_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('location_id')->references('id')->on('locations');
            $table->foreign('customer_id')->references('id')->on('customers');
        });
    }
}
