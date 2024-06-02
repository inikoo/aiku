<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 01 Jun 2024 23:26:05 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */


use App\Enums\Fulfilment\Rental\RentalStateEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('rental_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('rental_id')->index();
            $table->foreign('rental_id')->references('id')->on('rentals');

            $table->unsignedInteger('number_historic_assets')->default(0);
            foreach (RentalStateEnum::cases() as $case) {
                $table->unsignedInteger('number_rentals_state_'.$case->snake())->default(0);
            }

            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('rental_stats');
    }
};
