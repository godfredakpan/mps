<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountsTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('accounts');
    }

    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('name');
            $table->string('type');
            $table->string('reference');
            $table->text('details')->nullable();
            $table->decimal('opening_balance', 25, 4);
            $table->boolean('offline')->default(0);
            $table->boolean('fees')->nullable()->default(0);
            $table->decimal('fixed', 15, 4)->nullable()->default(0);
            $table->decimal('percentage', 10, 4)->nullable()->default(0);
            $table->string('apply_to', 5)->nullable();
            $table->timestamps();
        });
    }
}
