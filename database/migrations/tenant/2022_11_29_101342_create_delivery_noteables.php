<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('delivery_noteables', function (Blueprint $table) {
            $table->unsignedBigInteger('delivery_note_id');
            $table->foreign('delivery_note_id')->references('id')->on('delivery_notes');
            $table->morphs('delivery_noteable');
            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('delivery_noteables');
    }
};
