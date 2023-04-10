<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 19 Sept 2022 23:17:38 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('central_user_id');
            $table->foreign('central_user_id')->references('id')->on('central.central_users');
            $table->string('username')->unique();
            $table->boolean('status')->default(true);
            $table->nullableMorphs('parent');
            $table->string('email')->nullable();
            $table->string('about')->nullable();
            $table->rememberToken();
            $table->jsonb('data');
            $table->jsonb('settings');
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->string('password');
            $table->unsignedInteger('source_id')->nullable()->unique();
            $table->unsignedBigInteger('image_id')->nullable();
            $table->foreign('image_id')->references('id')->on('media');
        });
    }


    public function down()
    {
        Schema::dropIfExists('users');
    }
};
