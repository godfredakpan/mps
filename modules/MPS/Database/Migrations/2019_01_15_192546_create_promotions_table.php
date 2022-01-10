<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePromotionsTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('item_promotion');
        Schema::dropIfExists('category_promotion');
        Schema::dropIfExists('promotions');
    }

    public function up()
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('name');
            $table->string('type');
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->boolean('active')->default('1');
            $table->decimal('discount', 15, 4)->nullable();
            $table->string('discount_method', 20)->nullable();
            $table->boolean('show_on_receipt')->nullable();
            $table->decimal('amount_to_spend', 25, 4)->nullable();
            $table->uuid('item_id_to_buy')->nullable();
            $table->decimal('quantity_to_buy', 15, 4)->nullable();
            $table->uuid('item_id_to_get')->nullable();
            $table->decimal('quantity_to_get', 15, 4)->nullable();
            $table->integer('times_to_apply')->nullable();
            $table->string('coupon_code')->nullable();
            $table->text('details')->nullable();
            $table->timestamps();

            $table->foreign('item_id_to_buy')->references('id')->on('items');
            $table->foreign('item_id_to_get')->references('id')->on('items');
        });

        Schema::create('category_promotion', function (Blueprint $table) {
            $table->uuid('category_id')->index();
            $table->uuid('promotion_id')->index();
            $table->index(['promotion_id', 'category_id']);
            $table->unique(['promotion_id', 'category_id']);
            $table->foreign('promotion_id')->references('id')->on('promotions');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });

        Schema::create('item_promotion', function (Blueprint $table) {
            $table->uuid('item_id')->index();
            $table->uuid('promotion_id')->index();
            $table->index(['promotion_id', 'item_id']);
            $table->unique(['promotion_id', 'item_id']);
            $table->foreign('promotion_id')->references('id')->on('promotions');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
        });
    }
}
