<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('delivery_noteables', function (Blueprint $table) {
            $table->unsignedInteger('delivery_note_id');
            $table->foreign('delivery_note_id')->references('id')->on('delivery_notes');
            $table->string('delivery_noteable_type');
            $table->unsignedInteger('delivery_noteable_id');
            $table->timestampsTz();
            $table->unique(['delivery_noteable_type','delivery_noteable_id']);
        });
    }


    public function down()
    {
        Schema::dropIfExists('delivery_noteables');
    }
};
