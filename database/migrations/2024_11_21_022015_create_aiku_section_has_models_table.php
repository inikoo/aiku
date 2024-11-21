<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('aiku_section_has_models', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('aiku_section_id')->index();
            $table->foreign('aiku_section_id')->references('id')->on('aiku_sections')->onUpdate('cascade')->onDelete('cascade');
            $table->string('model_type')->index();
            $table->unsignedInteger('model_id')->index();

            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('aiku_section_has_models');
    }
};
