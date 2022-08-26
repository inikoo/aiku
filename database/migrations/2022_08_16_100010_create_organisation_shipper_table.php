<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 15:44:42 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('organisation_shipper', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained();
            $table->foreignId('shipper_id')->constrained();
            $table->jsonb('data');
            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('organisation_shipper');
    }
};
