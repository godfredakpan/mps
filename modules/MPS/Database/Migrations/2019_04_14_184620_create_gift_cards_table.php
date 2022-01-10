<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGiftCardsTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('gift_card_logs');
        Schema::dropIfExists('gift_cards');
    }

    public function up()
    {
        Schema::create('gift_cards', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('number');
            $table->decimal('amount', 25, 4);
            $table->decimal('balance', 25, 4);
            $table->date('expiry_date')->nullable();
            $table->uuid('customer_id')->nullable();
            $table->decimal('points', 25, 4)->nullable();
            $table->string('details')->nullable();
            $table->timestamps();

            $table->index('number');
            $table->index('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });

        Schema::create('gift_card_logs', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->decimal('amount', 25, 4);
            $table->uuid('gift_card_id')->index();
            $table->string('description')->nullable();
            $table->timestamps();

            $table->foreign('gift_card_id')->references('id')->on('gift_cards')->onDelete('cascade');
        });
    }
}
