<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-16 14:55
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('agent_warehouse_stocks', function (Blueprint $table) {
            $table->unsignedInteger('purchase_order_transaction_id')->nullable()->index();
            $table->foreign('purchase_order_transaction_id')->references('id')->on('purchase_order_transactions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('agent_warehouse_stocks', function (Blueprint $table) {
            $table->dropForeign(['purchase_order_transaction_id']);
            $table->dropColumn('purchase_order_transaction_id');
        });
    }
};
