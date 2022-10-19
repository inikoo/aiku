<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 18 Oct 2022 14:04:01 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('webnode_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->foreignId('webnode_id')->constrained();
            $table->timestampsTz();
        });
    }

    public function down()
    {
        Schema::dropIfExists('webnode_stats');
    }
};
