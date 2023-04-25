<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 26 Apr 2023 13:47:41 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('tenant_marketing_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('public.tenants')->onUpdate('cascade')->onDelete('cascade');

            $table->unsignedSmallInteger('number_shops')->default(0);

            $shopStates = ['in-process', 'open', 'closing-down', 'closed'];
            foreach ($shopStates as $shopState) {
                $table->unsignedSmallInteger('number_shops_state_'.preg_replace('/-/', '_', $shopState))->default(0);
            }
            $shopTypes = ['shop', 'fulfilment_house', 'agent'];
            foreach ($shopTypes as $shopType) {
                $table->unsignedSmallInteger('number_shops_type_'.$shopType)->default(0);
            }
            $shopSubtypes = ['b2b', 'b2c', 'storage', 'fulfilment', 'dropshipping'];
            foreach ($shopSubtypes as $shopSubtype) {
                $table->unsignedSmallInteger('number_shops_subtype_'.$shopSubtype)->default(0);
            }

            foreach ($shopStates as $shopState) {
                foreach ($shopSubtypes as $shopSubtype) {
                    $table->unsignedSmallInteger('number_shops_state_subtype_'.preg_replace('/-/', '_', $shopState).'_'.$shopSubtype)->default(0);
                }
            }

            $table->unsignedInteger('number_orphan_families')->default(0);

            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('tenant_marketing_stats');
    }
};
