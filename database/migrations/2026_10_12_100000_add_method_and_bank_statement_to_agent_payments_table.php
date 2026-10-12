<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 12 Oct 2026 10:00:00 Central European Summer Time, Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('agent_payments', function (Blueprint $table) {
            $table->string('method')->nullable();
            $table->unsignedInteger('media_id')->nullable();
            $table->foreign('media_id')->references('id')->on('media')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('agent_payments', function (Blueprint $table) {
            $table->dropForeign(['media_id']);
            $table->dropColumn(['method', 'media_id']);
        });
    }
};
