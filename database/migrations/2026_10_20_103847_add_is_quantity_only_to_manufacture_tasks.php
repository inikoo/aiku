<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Oct 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('manufacture_tasks', function (Blueprint $table) {
            $table->boolean('is_quantity_only')->default(false)->comment('true for hourly steps such as filling to order: the artisan records the quantity made with no timer, the session keeps who and when with no hours');
        });
    }

    public function down(): void
    {
        Schema::table('manufacture_tasks', function (Blueprint $table) {
            $table->dropColumn('is_quantity_only');
        });
    }
};
