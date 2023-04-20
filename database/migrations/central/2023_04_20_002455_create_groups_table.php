<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 20 Apr 2023 08:29:16 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('slug');
            $table->string('code');
            $table->string('name');
            $table->softDeletesTz();
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
