<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 01 Jun 2024 23:24:53 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Enums\Catalogue\Service\ServiceStateEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('service_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('service_id')->index();
            $table->foreign('service_id')->references('id')->on('services');

            $table->unsignedInteger('number_historic_assets')->default(0);
            foreach (ServiceStateEnum::cases() as $case) {
                $table->unsignedInteger('number_services_state_'.$case->snake())->default(0);
            }

            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('service_stats');
    }
};
