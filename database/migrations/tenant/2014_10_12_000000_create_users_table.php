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
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('group_user_id');
            $table->boolean('status')->default(true);
            $table->string('username')->unique()->comment('mirror group_users.username');
            $table->string('password')->comment('mirror group_users.password');
            $table->string('email')->nullable()->comment('mirror group_users.email');
            $table->nullableMorphs('parent');
            $table->rememberToken();
            $table->jsonb('data');
            $table->jsonb('settings');
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->unsignedInteger('source_id')->nullable()->unique();
            $table->unsignedBigInteger('image_id')->nullable();
            $table->foreign('image_id')->references('id')->on('media');
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
