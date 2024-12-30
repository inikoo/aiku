<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 21 Dec 2024 22:40:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Stubs\Migrations\HasSalesIntervals;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasSalesIntervals;
    public function up(): void
    {
        Schema::create('stock_intervals', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('stock_id')->index();
            $table->foreign('stock_id')->references('id')->on('stocks')->cascadeOnDelete();
            $table = $this->salesIntervalFields($table, [
                'grp_amount_revenue',
                'grp_amount_profit',
            ]);
            $table = $this->unsignedIntegerDateIntervals($table, [
                'dispatched',
                'org_stock_movements',
            ]);

            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('stock_intervals');
    }
};
