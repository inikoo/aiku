<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 17 Nov 2024 14:22:05 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('poll_options', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('poll_id')->index();
            $table->foreign('poll_id')->references('id')->on('polls');
            $table->string('slug')->unique()->collation('und_ns');
            $table->string('value')->index();
            $table->string('label');
            $table->timestampsTz();
            $table->datetimeTz('fetched_at')->nullable();
            $table->datetimeTz('last_fetched_at')->nullable();
            $table->softDeletesTz();
            $table->string('source_id')->nullable()->unique();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('poll_options');
    }
};
