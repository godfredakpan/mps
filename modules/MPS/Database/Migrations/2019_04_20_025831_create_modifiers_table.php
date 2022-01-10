<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateModifiersTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('modifier_sale_item');
        Schema::dropIfExists('item_modifier');
        Schema::dropIfExists('modifier_options');
        Schema::dropIfExists('modifiers');
    }

    public function up()
    {
        Schema::create('modifiers', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('code')->index()->unique();
            $table->string('title');
            $table->string('details')->nullable();
            $table->string('show_as')->nullable();
            $table->schemalessAttributes('extra_attributes');
            $table->timestamps();
        });
        Schema::create('modifier_options', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('sku');
            $table->uuid('item_id');
            $table->uuid('modifier_id');
            $table->decimal('quantity', 25, 4);
            $table->uuid('variation_id')->nullable();
            $table->timestamps();

            $table->index('sku');
            $table->unique('sku');
            $table->index('item_id');
            $table->index('modifier_id');
            $table->unique(['modifier_id', 'item_id']);
            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('variation_id')->references('id')->on('variations');
            $table->foreign('modifier_id')->references('id')->on('modifiers')->onDelete('cascade');
        });

        Schema::create('item_modifier', function (Blueprint $table) {
            $table->uuid('item_id');
            $table->uuid('modifier_id');

            $table->index('item_id');
            $table->index('modifier_id');
            $table->unique(['item_id', 'modifier_id']);
            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('modifier_id')->references('id')->on('modifiers');
        });

        Schema::create('modifier_sale_item', function (Blueprint $table) {
            $table->uuid('sale_item_id');
            $table->uuid('modifier_option_id');

            $table->index('sale_item_id');
            $table->index('modifier_option_id');
            $table->unique(['sale_item_id', 'modifier_option_id']);
            $table->foreign('sale_item_id')->references('id')->on('sale_items');
            $table->foreign('modifier_option_id')->references('id')->on('modifier_options');
        });
    }
}
