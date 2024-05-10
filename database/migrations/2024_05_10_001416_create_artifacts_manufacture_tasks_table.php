<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('artifacts_manufacture_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('artifact_id')->index();
            $table->foreign('artifact_id')->references('id')->on('artifacts');
            $table->unsignedInteger('manufacture_task_id')->index();
            $table->foreign('manufacture_task_id')->references('id')->on('manufacture_tasks');
            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('artifact_manufacture_tasks');
    }
};
