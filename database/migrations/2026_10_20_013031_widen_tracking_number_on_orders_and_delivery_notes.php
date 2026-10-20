<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Oct 2026 12:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        foreach (['orders', 'delivery_notes'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->text('tracking_number')->nullable()->comment('for search purposes')->change();
            });
        }
    }

    public function down(): void
    {
        foreach (['orders', 'delivery_notes'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('tracking_number')->nullable()->comment('for search purposes')->change();
            });
        }
    }
};
