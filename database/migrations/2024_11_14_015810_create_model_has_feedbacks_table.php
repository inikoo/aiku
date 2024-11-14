<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('model_has_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->morphs('model');
            $table->unsignedSmallInteger('feedback_id')->index();
            $table->foreign('feedback_id')->references('id')->on('feedbacks');
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('model_has_feedbacks');
    }
};
