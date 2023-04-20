<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 26 Oct 2022 13:09:12 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use App\Stubs\Migrations\HasProcurementStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasProcurementStats;

    public function up()
    {
        Schema::create('agent_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('agent_id')->index();
            $table->foreign('agent_id')->references('id')->on('agents');

            $table->unsignedSmallInteger('number_suppliers')->default(0);
            $table->unsignedSmallInteger('number_active_suppliers')->default(0);


            $table=$this->procurementStats($table);

            $table->timestampsTz();
        });
    }

    public function down()
    {
        Schema::dropIfExists('agent_stats');
    }
};
