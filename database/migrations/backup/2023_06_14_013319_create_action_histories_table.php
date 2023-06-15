<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 14 Jun 2023 11:52:02 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('action_histories', function (Blueprint $table) {
            $table->id();
            $table->string('index');
            $table->string('type');
            $table->jsonb('body');
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('action_histories');
    }
};
