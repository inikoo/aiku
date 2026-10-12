<?php

/*
 * Author: eka yudinata (https://github.com/ekayudinata)
 * Created: Monday, 12 Oct 2026 16:00:00 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2026, eka yudinata
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('mailshots', function (Blueprint $table) {
            $table->unsignedSmallInteger('ab_test_variant_delay_hours')->default(0);
            $table->unsignedSmallInteger('ab_test_tracking_hours')->default(24);
            $table->string('ab_test_winning_metric')->default('click_rate');
        });
    }


    public function down(): void
    {
        Schema::table('mailshots', function (Blueprint $table) {
            $table->dropColumn(['ab_test_variant_delay_hours', 'ab_test_tracking_hours', 'ab_test_winning_metric']);
        });
    }
};
