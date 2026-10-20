<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 19 Oct 2026 16:00:00 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('org_stocks', function (Blueprint $table) {
            $table->timestampTz('minimum_stock_alerted_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('org_stocks', function (Blueprint $table) {
            $table->dropColumn('minimum_stock_alerted_at');
        });
    }
};
