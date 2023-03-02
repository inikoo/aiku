<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 19 Sept 2022 23:25:05 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {
        Schema::create('central_users', function (Blueprint $table) {
            $table->id();
            $table->uuid('global_id')->unique();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('email')->nullable();
            $table->string('about')->nullable();
            $table->jsonb('tenants_data')->nullable();
            $table->unsignedSmallInteger('number_tenants')->default(0);
            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('central_users');
    }
};
