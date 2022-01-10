<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyModelOfAttachmentsTable extends Migration
{
    public function down()
    {
        Schema::table('attachments', function (Blueprint $table) {
            //
        });
    }

    public function up()
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->uuid('model_id', 50)->change();
        });
    }
}
