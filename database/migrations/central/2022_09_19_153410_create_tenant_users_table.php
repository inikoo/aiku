<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 03 Mar 2023 23:01:46 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('central_user_tenant', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('tenant_id')->index();
            $table->foreign('tenant_id')->references('id')->on('tenants')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedSmallInteger('central_user_id')->index();
            $table->foreign('central_user_id')->references('id')->on('central_users')->onUpdate('cascade')->onDelete('cascade');
            $table->unique(['tenant_id', 'central_user_id']);
            $table->boolean('status')->default('true')->index();
        });
    }


    public function down()
    {
        Schema::dropIfExists('central_user_tenant');
    }
};
