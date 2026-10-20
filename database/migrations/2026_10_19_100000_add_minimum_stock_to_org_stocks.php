<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 19 Oct 2026 10:00:00 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('org_stocks', function (Blueprint $table) {
            $table->decimal('minimum_stock', 16, 3)->nullable();
        });

        Schema::table('warehouse_stats', function (Blueprint $table) {
            $table->unsignedInteger('number_org_stocks_below_minimum_stock')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('org_stocks', function (Blueprint $table) {
            $table->dropColumn('minimum_stock');
        });

        Schema::table('warehouse_stats', function (Blueprint $table) {
            $table->dropColumn('number_org_stocks_below_minimum_stock');
        });
    }
};
