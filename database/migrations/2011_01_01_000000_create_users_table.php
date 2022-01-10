<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('user_settings');
        Schema::dropIfExists('users');
    }

    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('name');
            $table->string('username')->unique();
            $table->string('email')->nullable()->unique();
            $table->string('password');
            $table->string('avatar')->nullable();
            $table->string('locale')->nullable();
            $table->string('phone')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('active')->nullable();
            $table->boolean('employee')->nullable();
            $table->boolean('view_all')->nullable();
            $table->boolean('edit_all')->nullable();
            $table->boolean('bulk_actions')->nullable();
            $table->boolean('can_impersonate')->nullable();
            $table->schemalessAttributes('extra_attributes');
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('usermeta', function (Blueprint $table) {
            $table->string('meta_key');
            $table->text('meta_value')->nullable();
            $table->uuid('user_id');
            $table->string('module')->nullable();

            $table->index('meta_key');
            $table->index('user_id');
            $table->unique(['meta_key', 'user_id']);
            $table->foreign('user_id')->references('id')->on('users');
        });
    }
}
