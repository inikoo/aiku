<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 13 Dec 2022 02:50:19 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->foreign('current_historic_service_id')->references('id')->on('historic_services');
        });
    }


    public function down()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropForeign('current_historic_service_id');
        });
    }
};
