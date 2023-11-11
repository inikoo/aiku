<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 18 Apr 2023 14:59:23 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('shop_id')->index();
            $table->foreign('shop_id')->references('id')->on('shops');
            $table->unsignedSmallInteger('shipping_zone_schema_id')->index();
            $table->foreign('shipping_zone_schema_id')->references('id')->on('shipping_zone_schemas');
            $table->boolean('status');
            $table->string('slug')->unique()->collation('und_ns');
            $table->string('code');
            $table->string('name');
            $table->jsonb('territories');
            $table->jsonb('price');
            $table->softDeletesTz();
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('shipping_zones');
    }
};
