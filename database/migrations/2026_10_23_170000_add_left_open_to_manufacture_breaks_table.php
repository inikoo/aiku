<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 23 Oct 2026 17:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('manufacture_breaks', function (Blueprint $table) {
            $table->dateTimeTz('left_open_after_shift_at')->nullable()->index();
            $table->dateTimeTz('corrected_at')->nullable();
            $table->unsignedSmallInteger('corrected_by_user_id')->nullable();
            $table->foreign('corrected_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->dateTimeTz('original_ended_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('manufacture_breaks', function (Blueprint $table) {
            $table->dropForeign(['corrected_by_user_id']);
            $table->dropColumn(['left_open_after_shift_at', 'corrected_at', 'corrected_by_user_id', 'original_ended_at']);
        });
    }
};
