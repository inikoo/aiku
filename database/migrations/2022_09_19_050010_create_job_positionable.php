<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 07 Oct 2023 09:40:57 Malaysia Time, Office, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('job_positionables', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('job_position_id')->index();
            $table->foreign('job_position_id')->references('id')->on('job_positions');
            $table->string('job_positionable_type')->index();
            $table->unsignedSmallInteger('job_positionable_id')->index();
            $table->double('share')->nullable();
            $table->jsonb('scopes')->default('{}');
            $table->timestampsTz();
            $table->index(['job_positionable_type','job_positionable_id']);
            $table->unique(['job_position_id', 'job_positionable_id','job_positionable_type']);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('job_positionables');
    }
};
