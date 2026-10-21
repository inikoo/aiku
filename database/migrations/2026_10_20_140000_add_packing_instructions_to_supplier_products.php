<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-20 11:00
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('supplier_products', function (Blueprint $table) {
            $table->jsonb('packing_instructions')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('supplier_products', function (Blueprint $table) {
            $table->dropColumn('packing_instructions');
        });
    }
};
