<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Oct 2026 12:00:00 Central European Summer Time, Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('artisan_pay_type')->default('piece_rate');
        });

        Schema::table('manufacture_task_sessions', function (Blueprint $table) {
            $table->string('artisan_pay_type')->default('piece_rate')->index();
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('artisan_pay_type');
        });

        Schema::table('manufacture_task_sessions', function (Blueprint $table) {
            $table->dropColumn('artisan_pay_type');
        });
    }
};
