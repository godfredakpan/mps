<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMPSSettingsTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('mps_settings');
    }

    public function up()
    {
        Schema::create('mps_settings', function (Blueprint $table) {
            $table->string('mps_key');
            $table->primary('mps_key');
            $table->text('mps_value');
        });
    }
}
