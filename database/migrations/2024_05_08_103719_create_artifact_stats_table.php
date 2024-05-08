<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('artifact_stats', function (Blueprint $table) {
            $table->id();
            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('artifact_stats');
    }
};
