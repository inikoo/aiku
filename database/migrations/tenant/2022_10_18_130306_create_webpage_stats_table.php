<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 18 Oct 2022 14:05:44 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('webpage_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->foreignId('webpage_id')->constrained();
            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('webpage_stats');
    }
};
