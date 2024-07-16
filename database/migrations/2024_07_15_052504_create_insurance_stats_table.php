<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 15 Jul 2024 13:35:52 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('insurance_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('insurance_id')->index();
            $table->foreign('insurance_id')->references('id')->on('insurances');

            $table->unsignedInteger('number_historic_assets')->default(0);

            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('insurance_stats');
    }
};
