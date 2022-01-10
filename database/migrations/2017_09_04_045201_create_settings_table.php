<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettingsTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('settings');
    }

    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->string('tec_key');
            $table->primary('tec_key');
            $table->text('tec_value');
        });
    }
}
