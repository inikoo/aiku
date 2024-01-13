<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Nov 2023 23:22:59 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Enums\Market\Shop\ShopStateEnum;
use App\Enums\Market\Shop\ShopTypeEnum;
use App\Stubs\Migrations\HasCatalogueStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasCatalogueStats;

    public function up(): void
    {
        Schema::create('organisation_market_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('organisation_id');
            $table->foreign('organisation_id')->references('id')->on('organisations')->onUpdate('cascade')->onDelete('cascade');

            $table->unsignedSmallInteger('number_shops')->default(0);

            foreach (ShopStateEnum::cases() as $shopState) {
                $table->unsignedSmallInteger('number_shops_state_'.$shopState->snake())->default(0);
            }
            foreach (ShopTypeEnum::cases() as $shopType) {
                $table->unsignedSmallInteger('number_shops_type_'.$shopType->snake())->default(0);
            }


            $table = $this->catalogueStats($table);


            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('organisation_market_stats');
    }
};
