<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 25 Dec 2024 02:30:12 Malaysia Time, Kuala Lumpur, Malaysia
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
        Schema::create('master_asset_sales_intervals', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('master_asset_id')->index();
            $table->foreign('master_asset_id')->references('id')->on('master_assets');
            $table = $this->salesIntervalFields($table, [
                'sales_grp_currency',
                'invoices',
                'orders',
                'delivery_notes',
                'customers'
            ]);
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('master_asset_sales_intervals');
    }
};
