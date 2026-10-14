<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Oct 2026 13:00:00 Central European Summer Time, Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('manufacture_breaks', function (Blueprint $table) {
            $table->unsignedSmallInteger('planned_minutes')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('manufacture_breaks', function (Blueprint $table) {
            $table->unsignedSmallInteger('planned_minutes')->nullable(false)->change();
        });
    }
};
