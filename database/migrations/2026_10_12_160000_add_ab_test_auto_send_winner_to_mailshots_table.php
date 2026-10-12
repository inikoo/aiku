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
            $table->boolean('ab_test_auto_send_winner')->default(false);
        });
    }


    public function down(): void
    {
        Schema::table('mailshots', function (Blueprint $table) {
            $table->dropColumn('ab_test_auto_send_winner');
        });
    }
};
