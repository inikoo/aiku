<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 03 Mar 2023 22:40:07 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('central_users', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('username')->unique();
            $table->string('password');
            $table->string('email')->nullable();
            $table->string('about')->nullable();
            $table->jsonb('data')->nullable();
            $table->unsignedSmallInteger('number_tenants')->default(0);
            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('central_users');
    }
};
