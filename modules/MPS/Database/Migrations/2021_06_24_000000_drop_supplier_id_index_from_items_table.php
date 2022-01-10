<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropSupplierIdIndexFromItemsTable extends Migration
{
    public function up()
    {
        Schema::table('items', function (Blueprint $table) {
            // $table->dropForeign(['supplier_id']);
            // $table->dropUnique(['supplier_id']);
            // $table->index('supplier_id');
            // $table->foreign('supplier_id')->references('id')->on('suppliers');
        });
    }
}
