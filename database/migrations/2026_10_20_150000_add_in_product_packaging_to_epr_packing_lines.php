<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-20 14:02
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('epr_packing_lines', function (Blueprint $table) {
            $table->boolean('in_product_packaging')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('epr_packing_lines', function (Blueprint $table) {
            $table->dropColumn('in_product_packaging');
        });
    }
};
