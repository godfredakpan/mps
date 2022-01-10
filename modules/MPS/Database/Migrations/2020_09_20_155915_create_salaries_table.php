<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalariesTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('salaries');
    }

    public function up()
    {
        Schema::create('salaries', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->date('date');
            $table->string('type');
            $table->string('reference');
            $table->uuid('user_id')->index();
            $table->date('month')->nullable();
            $table->decimal('amount', 25, 4)->nullable();
            $table->uuid('account_id')->index()->nullable();
            $table->boolean('advance')->nullable()->default('0');
            $table->string('status')->nullable()->default('due');
            $table->decimal('work_hours', 15, 4)->nullable()->default('0');
            $table->decimal('work_hours_salary', 15, 4)->nullable()->default('0');
            $table->text('details')->nullable();
            $table->integer('points')->nullable();
            $table->uuid('transaction_id', 15, 4)->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('account_id')->references('id')->on('accounts');
        });
    }
}
