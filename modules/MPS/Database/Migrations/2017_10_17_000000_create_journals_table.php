<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJournalsTable extends Migration
{
    protected $guarded = ['id'];

    public function down()
    {
        Schema::dropIfExists('journals');
    }

    public function up()
    {
        Schema::create('journals', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->bigInteger('balance');
            $table->char('currency', 5);
            $table->uuid('morphed_id');
            $table->char('morphed_type', 32);
            $table->timestamps();

            $table->index(['morphed_id', 'morphed_type']);
        });
    }
}
