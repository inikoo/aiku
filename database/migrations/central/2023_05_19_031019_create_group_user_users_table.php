<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Fri, 19 May 2023 15:15:13 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('group_user_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('group_user_id');
            $table->unsignedSmallInteger('user_id');
            $table->jsonb('data');
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('group_user_user');
    }
};
