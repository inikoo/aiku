<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 21 Apr 2023 13:17:44 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Stubs\Migrations\HasProcurementStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasProcurementStats;
    public function up(): void
    {
        Schema::create('agent_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('agent_id')->index();
            $table->foreign('agent_id')->references('id')->on('agents');
            $table = $this->suppliersStats($table);
            $table = $this->supplierProductsStats($table);
            $table = $this->purchaseOrdersStats($table);
            $table = $this->supplierDeliveriesStats($table);
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_stats');
    }
};
