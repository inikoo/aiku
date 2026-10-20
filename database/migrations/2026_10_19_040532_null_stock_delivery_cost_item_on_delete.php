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
        Schema::table('stock_delivery_costs', function (Blueprint $table) {
            $table->dropForeign(['stock_delivery_item_id']);
            $table->foreign('stock_delivery_item_id')->references('id')->on('stock_delivery_items')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stock_delivery_costs', function (Blueprint $table) {
            $table->dropForeign(['stock_delivery_item_id']);
            $table->foreign('stock_delivery_item_id')->references('id')->on('stock_delivery_items');
        });
    }
};
