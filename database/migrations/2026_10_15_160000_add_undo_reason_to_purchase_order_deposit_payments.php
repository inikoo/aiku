<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 16:00:00 Central European Summer Time, Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('purchase_order_deposit_payments', function (Blueprint $table) {
            $table->text('undo_reason')->nullable();
            $table->unsignedSmallInteger('undone_by_id')->nullable();
            $table->foreign('undone_by_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_deposit_payments', function (Blueprint $table) {
            $table->dropForeign(['undone_by_id']);
            $table->dropColumn(['undo_reason', 'undone_by_id']);
        });
    }
};
