<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 06 Apr 2024 23:02:52 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('shipment_events', function (Blueprint $table) {
            $table->increments('id');
            $table->dateTimeTz('date');

            $table->unsignedInteger('shipment_id')->index();
            $table->foreign('shipment_id')->references('id')->on('shipments');
            $table->string('box')->nullable()->index();
            $table->string('code')->nullable()->index();
            $table->unsignedSmallInteger('status')->nullable()->index();
            $table->unsignedSmallInteger('state')->nullable()->index();
            $table->jsonb('data');
            $table->timestampsTz();
            $table->unique(['date','shipment_id','box','code']);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('shipment_events');
    }
};
