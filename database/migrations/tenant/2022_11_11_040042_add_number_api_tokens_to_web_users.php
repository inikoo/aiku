<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 11 Nov 2022 12:04:38 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {
        Schema::table('web_users', function (Blueprint $table) {
            $table->unsignedSmallInteger('number_api_tokens')->default(0);
        });
    }


    public function down()
    {
        Schema::table('web_users', function (Blueprint $table) {
            $table->dropColumn(['number_api_tokens']);
        });
    }
};
