<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-16 16:30
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('agent_goods_receipt_items', function (Blueprint $table) {
            $table->unsignedSmallInteger('group_id')->nullable()->index();
            $table->foreign('group_id')->references('id')->on('groups');
            $table->unsignedInteger('agent_warehouse_stock_id')->nullable()->index();
            $table->foreign('agent_warehouse_stock_id')->references('id')->on('agent_warehouse_stocks')->nullOnDelete();
            $table->decimal('quantity_rejected', 16, 3)->nullable();
            $table->text('reject_reason')->nullable();
            $table->timestampTz('checked_at')->nullable();
            $table->unsignedSmallInteger('checked_by_user_id')->nullable();
            $table->foreign('checked_by_user_id')->references('id')->on('users')->nullOnDelete();
        });

        DB::statement('UPDATE agent_goods_receipt_items SET group_id = agent_goods_receipts.group_id FROM agent_goods_receipts WHERE agent_goods_receipts.id = agent_goods_receipt_items.agent_goods_receipt_id');
        DB::statement('UPDATE agent_goods_receipt_items SET agent_warehouse_stock_id = agent_warehouse_stocks.id FROM agent_warehouse_stocks WHERE agent_warehouse_stocks.agent_goods_receipt_id = agent_goods_receipt_items.agent_goods_receipt_id AND agent_warehouse_stocks.purchase_order_transaction_id = agent_goods_receipt_items.purchase_order_transaction_id');

        Schema::table('agent_goods_receipt_items', function (Blueprint $table) {
            $table->unsignedSmallInteger('group_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('agent_goods_receipt_items', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropForeign(['agent_warehouse_stock_id']);
            $table->dropForeign(['checked_by_user_id']);
            $table->dropColumn(['group_id', 'agent_warehouse_stock_id', 'quantity_rejected', 'reject_reason', 'checked_at', 'checked_by_user_id']);
        });
    }
};
