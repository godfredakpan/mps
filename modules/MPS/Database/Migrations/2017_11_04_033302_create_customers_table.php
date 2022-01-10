<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomersTable extends Migration
{
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');
        });
        Schema::dropIfExists('customers');
        Schema::dropIfExists('customer_groups');
    }

    public function up()
    {
        Schema::create('customer_groups', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('code');
            $table->string('name');
            $table->decimal('discount', 10, 2);
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table) {
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
            $table->integer('points')->nullable()->default('0');
            $table->decimal('opening_balance', 20, 4)->default('0');
            $table->schemalessAttributes('extra_attributes');
            $table->uuid('user_id');
            $table->uuid('customer_group_id')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('customer_group_id')->references('id')->on('customer_groups');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->uuid('customer_id')->nullable()->after('can_impersonate');
            $table->index('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });
    }
}
