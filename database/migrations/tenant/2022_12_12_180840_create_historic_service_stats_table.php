<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 13 Dec 2022 02:08:55 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('historic_service_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('historic_service_id')->index();
            $table->foreign('historic_service_id')->references('id')->on('historic_services');
            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('historic_service_stats');
    }
};
