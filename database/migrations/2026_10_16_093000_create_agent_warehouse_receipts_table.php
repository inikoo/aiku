<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-16 09:30
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('agent_warehouse_receipts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('group_id')->index();
            $table->foreign('group_id')->references('id')->on('groups');
            $table->unsignedSmallInteger('agent_id')->index();
            $table->foreign('agent_id')->references('id')->on('agents');
            $table->unsignedSmallInteger('organisation_id')->index();
            $table->foreign('organisation_id')->references('id')->on('organisations');
            $table->unsignedInteger('purchase_order_id')->index();
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders');
            $table->unsignedInteger('purchase_order_transaction_id')->index();
            $table->foreign('purchase_order_transaction_id')->references('id')->on('purchase_order_transactions');
            $table->unsignedInteger('supplier_product_id')->index();
            $table->foreign('supplier_product_id')->references('id')->on('supplier_products');
            $table->unsignedInteger('agent_warehouse_stock_id')->nullable()->index();
            $table->foreign('agent_warehouse_stock_id')->references('id')->on('agent_warehouse_stocks')->nullOnDelete();
            $table->date('received_at');
            $table->decimal('quantity_received', 16, 3);
            $table->decimal('quantity_rejected', 16, 3)->nullable();
            $table->timestampTz('checked_at')->nullable();
            $table->text('reject_reason')->nullable();
            $table->unsignedSmallInteger('received_by_id')->nullable();
            $table->foreign('received_by_id')->references('id')->on('users')->nullOnDelete();
            $table->unsignedSmallInteger('checked_by_id')->nullable();
            $table->foreign('checked_by_id')->references('id')->on('users')->nullOnDelete();
            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_warehouse_receipts');
    }
};
