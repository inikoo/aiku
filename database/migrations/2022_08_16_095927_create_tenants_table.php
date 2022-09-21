<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 12 Aug 2022 18:34:11 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration

{
    public function up()
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->jsonb('data');
            $table->unsignedSmallInteger('country_id');
            $table->foreign('country_id')->references('id')->on('central.countries');
            $table->unsignedSmallInteger('language_id');
            $table->foreign('language_id')->references('id')->on('central.languages');
            $table->unsignedSmallInteger('timezone_id');
            $table->foreign('timezone_id')->references('id')->on('central.timezones');
            $table->unsignedSmallInteger('currency_id')->comment('tenant accounting currency');
            $table->foreign('currency_id')->references('id')->on('central.currencies');
            $table->timestampsTz();
            $table->softDeletesTz();
        });

    }


    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
