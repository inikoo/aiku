<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 09:00:00 Central European Summer Time, Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->timestampTz('production_started_at')->nullable();
            $table->jsonb('stage_marks')->default('{}');
        });

        Schema::table('stock_deliveries', function (Blueprint $table) {
            $table->timestampTz('arrived_at')->nullable();
            $table->jsonb('stage_marks')->default('{}');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['production_started_at', 'stage_marks']);
        });

        Schema::table('stock_deliveries', function (Blueprint $table) {
            $table->dropColumn(['arrived_at', 'stage_marks']);
        });
    }
};
