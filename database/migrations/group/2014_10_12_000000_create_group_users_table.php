<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 30 Apr 2023 14:21:55 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('group_users', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('username')->unique();
            $table->string('password');
            $table->string('email')->nullable();
            $table->string('name')->nullable();
            $table->string('about')->nullable();
            $table->unsignedInteger('media_id')->nullable();
            $table->jsonb('data')->nullable();
            $table->unsignedSmallInteger('number_users')->default(0);
            $table->unsignedSmallInteger('number_active_users')->default(0);
            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('group_users');
    }
};
