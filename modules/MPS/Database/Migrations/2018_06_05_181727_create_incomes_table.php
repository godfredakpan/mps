<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIncomesTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('incomes');
    }

    public function up()
    {
        Schema::create('incomes', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->date('date');
            $table->string('title');
            $table->string('reference');
            $table->decimal('amount', 25, 4);
            $table->text('details')->nullable();
            $table->uuid('user_id');
            $table->uuid('account_id');
            $table->uuid('location_id');
            $table->uuid('transaction_id')->nullable();
            $table->schemalessAttributes('extra_attributes');
            $table->unsignedInteger('number')->index();
            $table->timestamps();

            $table->index('user_id');
            $table->index('account_id');
            $table->index('location_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('account_id')->references('id')->on('accounts');
            $table->foreign('location_id')->references('id')->on('locations');
        });
    }
}
