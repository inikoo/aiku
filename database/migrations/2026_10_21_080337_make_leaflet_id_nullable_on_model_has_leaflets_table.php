<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 21 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('model_has_leaflets', function (Blueprint $table) {
            $table->unsignedInteger('leaflet_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('model_has_leaflets', function (Blueprint $table) {
            $table->unsignedInteger('leaflet_id')->nullable(false)->change();
        });
    }
};
