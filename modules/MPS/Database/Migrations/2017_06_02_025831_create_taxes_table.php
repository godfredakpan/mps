<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaxesTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('taxes');
        Schema::dropIfExists('taxables');
    }

    public function up()
    {
        Schema::create('taxes', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('name');
            $table->string('code', 20)->unique();
            $table->decimal('rate', 10, 4)->nullable();
            $table->text('details')->nullable();
            $table->string('number')->nullable();
            $table->boolean('state')->default('0');
            $table->boolean('same')->default('0');
            $table->boolean('compound')->default('0');
            $table->boolean('recoverable')->default('0');
            $table->timestamps();
        });

        Schema::create('taxables', function (Blueprint $table) {
            $table->uuid('tax_id');
            $table->uuid('taxable_id');
            $table->string('taxable_type');
            $table->boolean('recoverable')->nullable();
            $table->decimal('amount', 15, 4)->nullable();
            $table->decimal('total_amount', 15, 4)->nullable();
            $table->decimal('calculated_on', 15, 4)->nullable();

            $table->index('tax_id');
            $table->index(['taxable_id', 'taxable_type']);
        });
    }
}
