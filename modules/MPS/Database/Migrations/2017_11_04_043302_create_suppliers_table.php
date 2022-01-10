<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSuppliersTable extends Migration
{
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn('supplier_id');
        });
        Schema::dropIfExists('suppliers');
    }

    public function up()
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('name');
            $table->string('company')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('address')->nullable();
            $table->string('state_name')->nullable();
            $table->string('country_name')->nullable();
            $table->integer('max_due_amount')->nullable();
            $table->decimal('due_limit', 20, 4)->nullable();
            $table->decimal('opening_balance', 20, 4)->default('0');
            $table->schemalessAttributes('extra_attributes');
            $table->uuid('user_id');
            $table->timestamps();

            $table->index('user_id');
            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->uuid('supplier_id')->nullable()->after('can_impersonate');
            $table->index('supplier_id');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
        });
    }
}
