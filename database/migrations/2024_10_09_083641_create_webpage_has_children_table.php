<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('webpage_has_children', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('webpage_id')->index();
            $table->foreign('webpage_id')->references('id')->on('webpages');
            $table->unsignedInteger('child_id');
            $table->foreign('child')->references('id')->on('webpages');
            $table->timestampsTz();
            $table->index(['model_type', 'model_id']);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('webpage_has_children');
    }
};
