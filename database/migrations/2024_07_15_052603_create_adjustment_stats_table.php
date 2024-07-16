<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 15 Jul 2024 13:36:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('adjustment_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('adjustment_id')->index();
            $table->foreign('adjustment_id')->references('id')->on('adjustments');
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('adjustment_stats');
    }
};
