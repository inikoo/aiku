<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 09 Aug 2024 11:33:17 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('guest_has_job_positions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('group_id')->index();
            $table->foreign('group_id')->references('id')->on('groups')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedSmallInteger('guest_id')->index()->nullable();
            $table->foreign('guest_id')->references('id')->on('guests')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedSmallInteger('job_position_id')->index()->nullable();
            $table->foreign('job_position_id')->references('id')->on('job_positions')->onUpdate('cascade')->onDelete('cascade');
            $table->double('share')->nullable();
            $table->jsonb('scopes');
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_has_job_positions');
    }
};
