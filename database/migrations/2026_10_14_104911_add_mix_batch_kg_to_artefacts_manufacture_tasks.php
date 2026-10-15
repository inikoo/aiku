<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Oct 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('artefacts_manufacture_tasks', function (Blueprint $table) {
            $table->unsignedSmallInteger('mix_batch_kg')->nullable()->comment('kg steps only: mixes are made in whole batches of this many kg (25 or 50), the kg asked is rounded up to whole batches');
        });
    }

    public function down(): void
    {
        Schema::table('artefacts_manufacture_tasks', function (Blueprint $table) {
            $table->dropColumn('mix_batch_kg');
        });
    }
};
