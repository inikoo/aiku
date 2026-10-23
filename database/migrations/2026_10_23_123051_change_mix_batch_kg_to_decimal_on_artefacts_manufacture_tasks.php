<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 23 Oct 2026 20:30:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('artefacts_manufacture_tasks', function (Blueprint $table) {
            $table->decimal('mix_batch_kg', 8, 3)->nullable()->comment('kg steps only: mixes are made in whole batches of this many kg (one of the production mix batch sizes), the kg asked is rounded up to whole batches')->change();
        });
    }

    public function down(): void
    {
        Schema::table('artefacts_manufacture_tasks', function (Blueprint $table) {
            $table->unsignedSmallInteger('mix_batch_kg')->nullable()->comment('kg steps only: mixes are made in whole batches of this many kg (25 or 50), the kg asked is rounded up to whole batches')->change();
        });
    }
};
