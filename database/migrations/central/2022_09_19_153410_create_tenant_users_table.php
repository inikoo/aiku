<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 03 Mar 2023 23:01:46 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {
        Schema::create('tenant_users', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('tenant_id')->index();
            $table->foreign('tenant_id')->references('id')->on('tenants')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('central_users')->onUpdate('cascade')->onDelete('cascade');
            $table->unique(['tenant_id', 'user_id']);
            $table->boolean('status')->default('true')->index();
        });
    }


    public function down()
    {
        Schema::dropIfExists('tenant_users');
    }
};
