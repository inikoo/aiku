<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('trade_unit_has_ingredients', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('trade_unit_id');
            $table->foreign('trade_unit_id')->references('id')->on('trade_units')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedSmallInteger('ingredient_id');
            $table->foreign('ingredient_id')->references('id')->on('ingredients')->onUpdate('cascade')->onDelete('cascade');
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('trade_unit_has_ingredients');
    }
};
