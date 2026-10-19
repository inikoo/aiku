<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 18 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('agent_payments', function (Blueprint $table) {
            $table->string('type')->default('transfer_received')->index();
            $table->unsignedInteger('stock_delivery_id')->nullable()->change();
            $table->unsignedInteger('purchase_order_id')->nullable()->index();
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders');
            $table->unsignedInteger('supplier_id')->nullable()->index();
            $table->foreign('supplier_id')->references('id')->on('suppliers');
            $table->decimal('original_amount', 16)->nullable();
            $table->unsignedSmallInteger('original_currency_id')->nullable();
            $table->foreign('original_currency_id')->references('id')->on('currencies');
            $table->index(['agent_id', 'organisation_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::table('agent_payments', function (Blueprint $table) {
            $table->dropIndex(['agent_id', 'organisation_id', 'date']);
            $table->dropForeign(['original_currency_id']);
            $table->dropForeign(['supplier_id']);
            $table->dropForeign(['purchase_order_id']);
            $table->dropColumn(['type', 'purchase_order_id', 'supplier_id', 'original_amount', 'original_currency_id']);
            $table->unsignedInteger('stock_delivery_id')->nullable(false)->change();
        });
    }
};
