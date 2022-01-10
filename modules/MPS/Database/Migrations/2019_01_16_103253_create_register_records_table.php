<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRegisterRecordsTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('register_records');
    }

    public function up()
    {
        Schema::create('register_records', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->uuid('user_id');
            $table->uuid('location_id');
            $table->uuid('register_id');
            $table->decimal('total_cash_amount', 25, 4)->nullable();
            $table->decimal('total_cash_submitted', 25, 4)->nullable();
            $table->integer('total_cheques')->nullable();
            $table->decimal('total_cheques_amount', 25, 4)->nullable();
            $table->decimal('total_cheques_submitted', 25, 4)->nullable();
            $table->decimal('total_other_amount', 25, 4)->nullable();
            $table->decimal('total_refunds_amount', 25, 4)->nullable();
            $table->decimal('total_expenses_amount', 25, 4)->nullable();
            $table->decimal('total_gift_card_amount', 25, 4)->nullable();
            $table->decimal('total_return_orders_amount', 25, 4)->nullable();
            $table->integer('total_cc_slips')->nullable();
            $table->decimal('total_cc_slips_amount', 25, 4)->nullable();
            $table->decimal('total_cc_slips_submitted', 25, 4)->nullable();
            $table->decimal('cash_in_hand', 15, 4);
            $table->uuid('closed_by')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->uuid('transferred_to')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('location_id');
            $table->index('register_id');
            $table->index(['register_id', 'user_id']);
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('location_id')->references('id')->on('locations');
            $table->foreign('register_id')->references('id')->on('registers');
        });
    }
}
