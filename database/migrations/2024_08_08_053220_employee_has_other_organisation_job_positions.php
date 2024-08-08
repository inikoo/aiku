<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('employee_has_other_organisation_job_positions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('group_id');
            $table->foreign('group_id')->references('id')->on('groups')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedSmallInteger('organisation_id')->index()->nullable();
            $table->foreign('organisation_id')->references('id')->on('organisations')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedSmallInteger('employee_id')->index()->nullable();
            $table->foreign('employee_id')->references('id')->on('employees')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedSmallInteger('other_organisation_id')->index()->nullable();
            $table->foreign('other_organisation_id')->references('id')->on('organisations')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedSmallInteger('job_position_id')->index()->nullable();
            $table->foreign('job_position_id')->references('id')->on('job_positions')->onUpdate('cascade')->onDelete('cascade');
            $table->jsonb('scopes')->default('{}');
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_has_other_organisation_job_positions');
    }
};
