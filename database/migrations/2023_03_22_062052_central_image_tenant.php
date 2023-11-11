<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 22 Mar 2023 14:50:20 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('central_media_tenant', function (Blueprint $table) {
            $table->unsignedInteger('central_media_id')->index();
            $table->foreign('central_media_id')->references('id')->on('central_media');
            $table->unsignedSmallInteger('tenant_id')->index();
            $table->foreign('tenant_id')->references('id')->on('tenants');
            $table->timestampsTz();
            $table->unique(['central_media_id', 'tenant_id']);
        });
    }


    public function down()
    {
        Schema::dropIfExists('central_media_tenant');
    }
};
