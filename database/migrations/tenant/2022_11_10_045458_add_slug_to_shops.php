<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 10 Nov 2022 12:55:38 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->string('slug')->unique()->index()->nullable();
        });
    }


    public function down()
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn(['slug']);
        });
    }
};
