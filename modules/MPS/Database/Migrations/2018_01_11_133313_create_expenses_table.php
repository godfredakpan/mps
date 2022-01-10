<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExpensesTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('expenses');
    }

    public function up()
    {
        Schema::create('expenses', function (Blueprint $table) {
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
            $table->boolean('approved')->nullable();
            $table->uuid('approved_by_id')->nullable();
            $table->uuid('transaction_id')->nullable();
            $table->schemalessAttributes('extra_attributes');
            $table->unsignedInteger('number')->index();
            $table->boolean('recurring')->nullable();
            $table->uuid('expense_id')->nullable();
            $table->date('start_date')->nullable();
            $table->string('repeat', 30)->nullable();
            $table->integer('create_before')->nullable();
            $table->date('last_created_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('account_id');
            $table->index('location_id');
            $table->index('approved_by_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('approved_by_id')->references('id')->on('users');
            $table->foreign('account_id')->references('id')->on('accounts');
            $table->foreign('location_id')->references('id')->on('locations');
        });
    }
}
