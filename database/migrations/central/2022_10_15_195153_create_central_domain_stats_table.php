<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 15 Oct 2022 21:52:07 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('central_domain_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('central_domain_id');
            $table->foreign('central_domain_id')->references('id')->on('central.central_domains')->onDelete('cascade');
            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('central_domain_stats');
    }
};
