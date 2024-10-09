<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('web_block_has_models', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('web_block_id')->index();
            $table->foreign('web_block_id')->references('id')->on('web_blocks');
            $table->string('model_type')->index();
            $table->unsignedInteger('model_id');
            $table->timestampsTz();
            $table->index(['model_type', 'model_id']);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('web_block_has_models');
    }
};
