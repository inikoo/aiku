<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 19 Sept 2022 23:17:38 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->boolean('status')->default(true);
            $table->nullableMorphs('parent');
            $table->string('email')->nullable();
            $table->string('about')->nullable();
            $table->rememberToken();
            $table->jsonb('tenants_data')->nullable();
            $table->jsonb('profile');
            $table->jsonb('settings');
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->string('password');
            $table->unsignedSmallInteger('number_tenants')->default(0);
            $table->uuid('global_id')->index();
            $table->unsignedBigInteger('source_id')->nullable()->unique();

        });
    }


    public function down()
    {
        Schema::dropIfExists('users');
    }
};
