<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComboPortionsTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('portion_choosable_items');
        Schema::dropIfExists('portion_choosables');
        Schema::dropIfExists('portion_essentials');
    }

    public function up()
    {
        Schema::create('portion_essentials', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->uuid('item_id');
            $table->uuid('portion_id');
            $table->uuid('variation_id')->nullable();
            $table->decimal('quantity', 25, 4);

            $table->index('item_id');
            $table->index('portion_id');
            $table->index('variation_id');
            $table->unique(['portion_id', 'item_id']);
            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('variation_id')->references('id')->on('variations');
            $table->foreign('portion_id')->references('id')->on('portions')->onDelete('cascade');
        });

        Schema::create('portion_choosables', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('name');
            $table->uuid('portion_id');

            $table->index('portion_id');
            $table->foreign('portion_id')->references('id')->on('portions');
        });

        Schema::create('portion_choosable_items', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->uuid('item_id');
            $table->uuid('portion_choosable_id');
            $table->uuid('variation_id')->nullable();
            $table->decimal('quantity', 25, 4);

            $table->index('item_id');
            $table->index('variation_id');
            $table->index('portion_choosable_id');
            $table->unique(['portion_choosable_id', 'item_id', 'variation_id'], 'portion_choosable_items_pci_ii_vi_unique');
            $table->foreign('item_id')->references('id')->on('items');
            $table->foreign('variation_id')->references('id')->on('variations');
            $table->foreign('portion_choosable_id')->references('id')->on('portion_choosables');
        });
    }
}
