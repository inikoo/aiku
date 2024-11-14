<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('model_has_dispatched_emails', function (Blueprint $table) {
            $table->id();
            $table->morphs('model');
            $table->unsignedSmallInteger('dispatched_email_id')->index();
            $table->foreign('dispatched_email_id')->references('id')->on('dispatched_emails');
            $table->unsignedSmallInteger('outbox_id')->index();
            $table->foreign('outbox_id')->references('id')->on('outboxes');


            $table->timestampsTz();
            $table->datetimeTz('fetched_at')->nullable();
            $table->datetimeTz('last_fetched_at')->nullable();
            $table->string('source_id')->nullable()->unique();

        });
    }


    public function down(): void
    {
        Schema::dropIfExists('model_has_dispatched_emails');
    }
};
