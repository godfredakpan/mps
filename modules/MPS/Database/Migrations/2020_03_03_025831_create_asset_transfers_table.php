<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAssetTransfersTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('asset_transfers');
    }

    public function up()
    {
        Schema::create('asset_transfers', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('reference');
            $table->decimal('amount', 25, 4);
            $table->uuid('to')->index();
            $table->uuid('from')->index();
            $table->uuid('user_id')->index();
            $table->text('details')->nullable();
            $table->string('to_transaction_id')->nullable();
            $table->string('from_transaction_id')->nullable();
            $table->schemalessAttributes('extra_attributes');
            $table->unsignedInteger('number')->index();
            $table->timestamps();

            $table->foreign('to')->references('id')->on('accounts');
            $table->foreign('from')->references('id')->on('accounts');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }
}
