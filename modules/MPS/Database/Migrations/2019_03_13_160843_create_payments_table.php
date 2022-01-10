<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentsTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('payment_purchase');
        Schema::dropIfExists('payment_sale');
        Schema::dropIfExists('payments');
    }

    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('reference');
            $table->decimal('amount', 25, 4);
            $table->text('details')->nullable();
            $table->string('gateway')->nullable();
            $table->boolean('received')->default(0);
            $table->string('hash')->nullable();
            $table->string('file')->nullable();
            $table->boolean('review')->nullable();
            $table->uuid('reviewed_by')->nullable();
            $table->uuid('location_id');
            $table->uuid('sale_id')->nullable();
            $table->uuid('user_id')->nullable();
            $table->uuid('account_id')->nullable();
            $table->uuid('purchase_id')->nullable();
            $table->uuid('payable_id')->nullable();
            $table->string('payable_type')->nullable();
            $table->uuid('return_id')->nullable();
            $table->string('card_holder')->nullable();
            $table->string('card_number')->nullable();
            $table->string('expiry_date')->nullable();
            $table->string('cheque_number')->nullable();
            $table->string('gift_card_number')->nullable();
            $table->uuid('payer_transaction_id')->nullable();
            $table->uuid('account_transaction_id')->nullable();
            $table->string('gateway_transaction_id')->nullable();
            $table->schemalessAttributes('extra_attributes');
            $table->unsignedInteger('number')->index();
            $table->timestamps();

            $table->index('hash');
            $table->index('sale_id');
            $table->index('user_id');
            $table->index('account_id');
            $table->index('location_id');
            $table->index('purchase_id');
            $table->index(['payable_id', 'payable_type']);
            $table->foreign('sale_id')->references('id')->on('sales');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('account_id')->references('id')->on('accounts');
            $table->foreign('location_id')->references('id')->on('locations');
            $table->foreign('purchase_id')->references('id')->on('purchases');
        });

        Schema::create('payment_sale', function (Blueprint $table) {
            $table->decimal('amount', 25, 4);
            $table->uuid('sale_id')->nullable();
            $table->uuid('payment_id')->nullable();

            $table->index('sale_id');
            $table->index('payment_id');
            $table->index(['sale_id', 'payment_id']);
            $table->unique(['sale_id', 'payment_id']);
            $table->foreign('payment_id')->references('id')->on('payments');
            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
        });

        Schema::create('payment_purchase', function (Blueprint $table) {
            $table->decimal('amount', 25, 4);
            $table->uuid('purchase_id')->nullable();
            $table->uuid('payment_id')->nullable();

            $table->index('purchase_id');
            $table->index('payment_id');
            $table->index(['purchase_id', 'payment_id']);
            $table->unique(['purchase_id', 'payment_id']);
            $table->foreign('payment_id')->references('id')->on('payments');
            $table->foreign('purchase_id')->references('id')->on('purchases')->onDelete('cascade');
        });
    }
}
