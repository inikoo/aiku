<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 15 Oct 2022 21:52:07 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('central_domain_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('central_domain_id')->constrained();
            $table->timestamps();
        });
    }


    public function down()
    {
        Schema::dropIfExists('central_domain_stats');
    }
};
