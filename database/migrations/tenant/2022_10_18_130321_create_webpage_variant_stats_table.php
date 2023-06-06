<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 18 Oct 2022 14:04:01 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('webpage_variant_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('webpage_variant_id')->index();
            $table->foreign('webpage_variant_id')->references('id')->on('webpage_variants');
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webpage_variant_stats');
    }
};
