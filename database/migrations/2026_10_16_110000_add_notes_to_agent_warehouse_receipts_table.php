<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-16 11:01
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('agent_warehouse_receipts', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('quantity_received');
        });
    }

    public function down(): void
    {
        Schema::table('agent_warehouse_receipts', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
    }
};
