<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 16 Jul 2024 21:11:07 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('shipping_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('shipping_id')->index();
            $table->foreign('shipping_id')->references('id')->on('shippings');
            $table->unsignedInteger('number_historic_assets')->default(0);
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('shipping_stats');
    }
};
