<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 13 Oct 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('manufacture_tasks', function (Blueprint $table) {
            $table->boolean('is_recorded_in_kg')->default(false)->comment('true for steps such as mixing: the quantity made is entered in kg and units_per_artefact of the recipe step is the kg one artefact takes');
        });
    }

    public function down(): void
    {
        Schema::table('manufacture_tasks', function (Blueprint $table) {
            $table->dropColumn('is_recorded_in_kg');
        });
    }
};
