<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 21 Dec 2024 22:36:36 Malaysia Time, Kuala Lumpur, Malaysia
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
        Schema::create('org_stock_family_intervals', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('org_stock_family_id')->index();
            $table->foreign('org_stock_family_id')->references('id')->on('org_stock_families');
            $table = $this->salesIntervalFields($table, [
                'org_amount_revenue',
                'grp_amount_revenue',
                'org_amount_profit',
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
        Schema::dropIfExists('org_stock_family_intervals');
    }
};
