<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 12 Sept 2022 23:18:30 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {
        Schema::create('job_position_organisation', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_position_id')->index();
            $table->foreign('job_position_id')->references('id')->on('job_positions');
            $table->unsignedBigInteger('organisation_id')->index();
            $table->foreign('organisation_id')->references('id')->on('organisations');
            $table->unsignedSmallInteger('number_employees')->default(0);
            $table->double('number_work_time')->default(0);
            $table->decimal('share_work_time', 7, 6)->nullable();
            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('job_position_organisation');
    }
};
