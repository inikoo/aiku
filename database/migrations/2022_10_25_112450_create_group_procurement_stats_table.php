<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 14 May 2023 10:11:27 Malaysia Time, Kuala Lumpur, Malaysia
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
        Schema::create('group_procurement_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('group_id');
            $table->foreign('group_id')->references('id')->on('public.groups')->onUpdate('cascade')->onDelete('cascade');
            $table = $this->agentStats($table);
            $table = $this->suppliersStats($table);
            $table = $this->supplierProductsStats($table);
            $table = $this->purchaseOrdersStats($table);
            $table = $this->supplierDeliveriesStats($table);
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_procurement_stats');
    }
};
