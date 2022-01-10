<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVariationsTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('variation_stock');
        Schema::dropIfExists('variations');
    }

    public function up()
    {
        Schema::create('variations', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('sku');
            $table->uuid('item_id');
            $table->string('code')->nullable();
            $table->string('rack')->nullable();
            $table->decimal('cost', 25, 4)->nullable();
            $table->decimal('price', 25, 4)->nullable();
            $table->decimal('quantity', 25, 4)->nullable();
            $table->string('dimensions')->nullable();
            $table->string('weight')->nullable();
            $table->json('meta')->nullable();

            $table->index('sku');
            $table->unique('sku');
            $table->index('code');
            $table->index('item_id');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
        });

        Schema::create('variation_stocks', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->uuid('location_id');
            $table->uuid('variation_id');
            $table->string('rack')->nullable();
            $table->decimal('cost', 25, 4)->nullable();
            $table->decimal('price', 25, 4)->nullable();
            $table->decimal('avg_cost', 25, 4)->nullable();
            $table->decimal('quantity', 25, 4)->nullable();

            $table->index('location_id');
            $table->index('variation_id');
            $table->unique(['location_id', 'variation_id']);
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');
            $table->foreign('variation_id')->references('id')->on('variations')->onDelete('cascade');
        });
    }
}
