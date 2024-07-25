<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 12:01:35 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

use App\Stubs\Migrations\HasFulfilmentStats;
use App\Stubs\Migrations\HasLocationsStats;
use App\Stubs\Migrations\HasSalesStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasLocationsStats;
    use HasFulfilmentStats;
    use HasSalesStats;

    public function up(): void
    {
        Schema::create('warehouse_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('warehouse_id')->index();
            $table->foreign('warehouse_id')->references('id')->on('warehouses');

            $table->unsignedSmallInteger('number_warehouse_areas')->default(0);
            $table = $this->locationsStats($table);

            $table = $this->deliveryNotesStatsFields($table);


            $table->unsignedSmallInteger('number_fulfilments')->default(0);
            $table = $this->fulfilmentCustomersStats($table);
            $table = $this->fulfilmentStats($table);

            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_stats');
    }
};
