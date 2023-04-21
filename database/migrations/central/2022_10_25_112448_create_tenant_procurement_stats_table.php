<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 03 Mar 2023 23:08:39 Malaysia Time, Kuala Lumpur, Malaysia
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
        Schema::create('tenant_procurement_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onUpdate('cascade')->onDelete('cascade');

            $table->unsignedInteger('number_suppliers')->default(0);
            $table->unsignedInteger('number_active_suppliers')->default(0);

            $table->unsignedSmallInteger('number_agents')->default(0);
            $table->unsignedSmallInteger('number_active_agents')->default(0);

            $table = $this->procurementStats($table);

            $table->unsignedSmallInteger('number_workshops')->default(0);


            $table->timestampsTz();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tenant_procurement_stats');
    }
};
