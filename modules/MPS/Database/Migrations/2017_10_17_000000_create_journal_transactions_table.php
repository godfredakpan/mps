<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJournalTransactionsTable extends Migration
{
    protected $guarded = ['id'];

    public function down()
    {
        Schema::dropIfExists('journal_transactions');
    }

    public function up()
    {
        Schema::create('journal_transactions', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->uuid('journal_id');
            $table->bigInteger('debit')->nullable();
            $table->bigInteger('credit')->nullable();
            $table->char('currency', 5);
            $table->text('memo')->nullable();
            $table->string('type', 20)->nullable()->index();
            $table->uuid('subject_id')->nullable();
            $table->string('subject_type')->nullable();
            $table->timestamp('post_date');
            $table->uuid('journal_transaction_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('journal_id');
            $table->index(['subject_id', 'subject_type']);
        });
    }
}
