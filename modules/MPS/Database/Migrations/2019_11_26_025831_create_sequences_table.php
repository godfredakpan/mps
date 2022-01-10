<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSequencesTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('sequences');
    }

    public function up()
    {
        Schema::create('sequences', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->integer('sale');
            $table->integer('order');
            $table->integer('income');
            $table->integer('salary');
            $table->integer('account');
            $table->integer('expense');
            $table->integer('payment');
            $table->integer('delivery');
            $table->integer('purchase');
            $table->integer('quotation');
            $table->integer('return_order');
            $table->integer('recurring_sale');
            $table->integer('asset_transfer');
            $table->integer('stock_transfer');
            $table->integer('stock_adjustment');
            $table->timestamp('last_reset_date')->useCurrent();
        });
    }
}
