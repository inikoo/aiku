<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 02 Apr 2024 09:24:05 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Enums\Fulfilment\PalletReturn\PalletReturnItemStateEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('pallet_return_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('pallet_id');
            $table->foreign('pallet_id')->references('id')->on('pallets');
            $table->unsignedInteger('pallet_return_id');
            $table->foreign('pallet_return_id')->references('id')->on('pallet_returns');
            $table->unsignedInteger('picked_from_location_id')->nullable();
            $table->foreign('picked_from_location_id')->references('id')->on('locations');
            $table->string('state')->default(PalletReturnItemStateEnum::IN_PROCESS->value);
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('pallet_return_items');
    }
};
