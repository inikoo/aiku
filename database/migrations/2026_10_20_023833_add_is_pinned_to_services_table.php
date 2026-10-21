<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 20 Oct 2026 10:38:33 Central Indonesia Time, Bali, Indonesia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->boolean('is_pinned')->default(false)->index();
        });
    }


    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('is_pinned');
        });
    }
};
