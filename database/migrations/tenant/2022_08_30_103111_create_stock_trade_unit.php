<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 18:49:27 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('stock_trade_unit', function (Blueprint $table) {
            $table->unsignedInteger('stock_id')->nullable();
            $table->foreign('stock_id')->references('id')->on('stocks');
            $table->unsignedInteger('trade_unit_id')->nullable();
            $table->decimal('quantity', 12, 3)->default(1);
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_trade_unit');
    }
};
