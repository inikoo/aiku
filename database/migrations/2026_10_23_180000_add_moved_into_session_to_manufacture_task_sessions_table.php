<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 23 Oct 2026 20:30:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('manufacture_task_sessions', function (Blueprint $table) {
            $table->unsignedBigInteger('moved_into_session_id')->nullable()->index();
            $table->foreign('moved_into_session_id')->references('id')->on('manufacture_task_sessions');
            $table->unsignedInteger('moved_seconds')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('manufacture_task_sessions', function (Blueprint $table) {
            $table->dropForeign(['moved_into_session_id']);
            $table->dropColumn(['moved_into_session_id', 'moved_seconds']);
        });
    }
};
