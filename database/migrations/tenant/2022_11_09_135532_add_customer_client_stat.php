<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 09 Nov 2022 21:55:55 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::table('customer_stats', function (Blueprint $table) {
            $table->unsignedBigInteger('number_clients')->default(0);
            $table->unsignedBigInteger('number_active_clients')->default(0);

        });
    }


    public function down()
    {
        Schema::table('customer_stats', function (Blueprint $table) {
            $table->dropColumn(['number_clients', 'number_active_clients']);


        });
    }
};
