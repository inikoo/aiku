<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 03 Mar 2023 23:08:39 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('central_domains', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->unsignedBigInteger('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedMediumInteger('website_id')->index();
            $table->string('domain')->index();
            $table->enum('state',['created','iris-enabled'])->default('created');
            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('central_domains');
    }
};
