<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivityLogTable extends Migration
{
    public function down()
    {
        Schema::drop('activity_log');
    }

    public function up()
    {
        Schema::create('activity_log', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->text('description');
            $table->string('log_name')->nullable();
            $table->uuid('subject_id')->nullable();
            $table->string('subject_type')->nullable();
            $table->uuid('causer_id')->nullable();
            $table->string('causer_type')->nullable();
            $table->longText('properties')->nullable();
            $table->timestamps();

            $table->index('log_name');
            $table->index(['causer_id', 'causer_type']);
            $table->index(['subject_id', 'subject_type']);
        });
    }
}
