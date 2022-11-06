<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 17 Oct 2022 17:59:56 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('tenant_marketing_stats', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onUpdate('cascade')->onDelete('cascade');

            $table->unsignedSmallInteger('number_shops')->default(0);

            $shopTypes = ['shop', 'fulfilment_house','agent'];
            foreach ($shopTypes as $shopType) {
                $table->unsignedSmallInteger('number_shops_type_'.$shopType)->default(0);
            }
            $shopSubtypes = ['b2b', 'b2c', 'storage', 'fulfilment', 'dropshipping'];
            foreach ($shopSubtypes as $shopSubtype) {
                $table->unsignedSmallInteger('number_shops_subtype_'.$shopSubtype)->default(0);
            }

            $table->unsignedBigInteger('number_orphan_families')->default(0);

            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('tenant_marketing_stats');
    }
};
