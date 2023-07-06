<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 26 Apr 2023 13:47:41 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Enums\Market\Shop\ShopStateEnum;
use App\Enums\Market\Shop\ShopSubtypeEnum;
use App\Enums\Market\Shop\ShopTypeEnum;
use App\Stubs\Migrations\HasCatalogueStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasCatalogueStats;

    public function up(): void
    {
        Schema::create('tenant_market_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('public.tenants')->onUpdate('cascade')->onDelete('cascade');

            $table->unsignedSmallInteger('number_shops')->default(0);

            foreach (ShopStateEnum::cases() as $shopState) {
                $table->unsignedSmallInteger('number_shops_state_'.$shopState->snake())->default(0);
            }
            foreach (ShopTypeEnum::cases() as $shopType) {
                $table->unsignedSmallInteger('number_shops_type_'.$shopType->snake())->default(0);
            }
            foreach (ShopSubtypeEnum::cases() as $shopSubtype) {
                $table->unsignedSmallInteger('number_shops_subtype_'.$shopSubtype->snake())->default(0);
            }

            foreach (ShopStateEnum::cases() as $shopState) {
                foreach (ShopSubtypeEnum::cases() as $shopSubtype) {
                    $table->unsignedSmallInteger('number_shops_state_subtype_'.$shopState->snake().'_'.$shopSubtype->snake())->default(0);
                }
            }

            $table = $this->catalogueStats($table);


            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('tenant_market_stats');
    }
};
