<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Created: Thu, 22 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('shop_crm_stats', function (Blueprint $table) {
            $table->jsonb('customer_value_curve')->nullable();
            $table->timestampTz('customer_value_curve_hydrated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('shop_crm_stats', function (Blueprint $table) {
            $table->dropColumn(['customer_value_curve', 'customer_value_curve_hydrated_at']);
        });
    }
};
