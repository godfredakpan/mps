<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUnitPricesTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('unit_prices');
    }

    public function up()
    {
        Schema::create('unit_prices', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->uuid('unit_id');
            $table->uuidMorphs('subject');
            $table->decimal('cost', 25, 4);
            $table->decimal('price', 25, 4);

            $table->index('unit_id');
            $table->foreign('unit_id')->references('id')->on('units');
        });
    }
}
