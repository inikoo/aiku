<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 20:00:00 Central European Summer Time, Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('manufacture_task_sessions', function (Blueprint $table) {
            $table->timestampTz('original_started_at')->nullable();
            $table->timestampTz('original_ended_at')->nullable();
            $table->string('time_edit_reason')->nullable();
            $table->text('time_edit_note')->nullable();
            $table->unsignedInteger('time_edited_by')->nullable();
            $table->timestampTz('time_edited_at')->nullable()->index();
            $table->timestampTz('left_open_after_shift_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('manufacture_task_sessions', function (Blueprint $table) {
            $table->dropColumn(['original_started_at', 'original_ended_at', 'time_edit_reason', 'time_edit_note', 'time_edited_by', 'time_edited_at', 'left_open_after_shift_at']);
        });
    }
};
