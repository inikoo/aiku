<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('agent_payments', function (Blueprint $table) {
            $table->unsignedInteger('org_stock_id')->nullable()->index();
            $table->foreign('org_stock_id')->references('id')->on('org_stocks');
        });

        Schema::table('org_agents', function (Blueprint $table) {
            $table->decimal('agent_fee_percentage', 5)->nullable();
        });

        Schema::table('stock_delivery_costs', function (Blueprint $table) {
            $table->unsignedBigInteger('stock_delivery_item_id')->nullable()->index();
            $table->foreign('stock_delivery_item_id')->references('id')->on('stock_delivery_items');
        });
    }

    public function down(): void
    {
        Schema::table('stock_delivery_costs', function (Blueprint $table) {
            $table->dropForeign(['stock_delivery_item_id']);
            $table->dropColumn('stock_delivery_item_id');
        });

        Schema::table('org_agents', function (Blueprint $table) {
            $table->dropColumn('agent_fee_percentage');
        });

        Schema::table('agent_payments', function (Blueprint $table) {
            $table->dropForeign(['org_stock_id']);
            $table->dropColumn('org_stock_id');
        });
    }
};
