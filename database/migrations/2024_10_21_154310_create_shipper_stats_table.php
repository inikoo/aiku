<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 21 Oct 2024 23:45:49 Central Indonesia Time, Office, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('shipper_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('shipper_id')->index();
            $table->foreign('shipper_id')->references('id')->on('shippers');
            $table->unsignedInteger('number_customers')->default(0);
            $table->unsignedInteger('number_delivery_notes')->default(0);
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('shipper_stats');
    }
};
