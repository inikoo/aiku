<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-16 13:50
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('stock_delivery_items', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_delivery_items', 'cbm_in_container')) {
                $table->decimal('cbm_in_container', 16, 4)->nullable()->comment('cubic meters the line takes in the container, null means the cbm of its cartons');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stock_delivery_items', function (Blueprint $table) {
            if (Schema::hasColumn('stock_delivery_items', 'cbm_in_container')) {
                $table->dropColumn('cbm_in_container');
            }
        });
    }
};
