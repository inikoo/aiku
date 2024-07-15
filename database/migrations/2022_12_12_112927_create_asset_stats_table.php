<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 12 Dec 2022 19:37:02 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use App\Enums\Catalogue\Asset\AssetTypeEnum;
use App\Enums\Catalogue\Asset\AssetStateEnum;
use App\Stubs\Migrations\HasSalesIntervals;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasSalesIntervals;

    public function up(): void
    {
        Schema::create('asset_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('asset_id')->index();
            $table->foreign('asset_id')->references('id')->on('assets');


            $table->unsignedSmallInteger('number_historic_assets')->default(0);

            $table->unsignedInteger('number_assets_type_product')->default(0);
            $table->unsignedInteger('number_assets_type_service')->default(0);
            $table->unsignedInteger('number_assets_type_subscription')->default(0);
            $table->unsignedInteger('number_assets_type_rental')->default(0);

            foreach (AssetStateEnum::cases() as $case) {
                $table->unsignedInteger('number_assets_state_'.$case->snake())->default(0);
            }

            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('asset_stats');
    }
};
