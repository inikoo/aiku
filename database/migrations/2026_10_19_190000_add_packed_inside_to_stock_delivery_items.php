<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('stock_delivery_items', function (Blueprint $table) {
            $table->foreignId('packed_inside_stock_delivery_item_id')->nullable()->index()
                ->comment('the line of the same container this one is packed inside, it takes no space of its own')
                ->constrained('stock_delivery_items')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stock_delivery_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('packed_inside_stock_delivery_item_id');
        });
    }
};
