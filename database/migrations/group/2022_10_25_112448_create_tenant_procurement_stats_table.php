<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 26 Apr 2023 13:47:45 Malaysia Time, Sanur, Bali, Indonesia
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
            $table->foreign('tenant_id')->references('id')->on('public.tenants')->onUpdate('cascade')->onDelete('cascade');

            $table->unsignedInteger('number_suppliers')->default(0)->comment('Number of active suppliers');
            $table->unsignedInteger('number_agents')->default(0)->comment('Number of active agents');


            $table->unsignedInteger('suppliers_count')->default(0)->comment('Total number of suppliers records attached to tenant');
            $table->unsignedInteger('agents_count')->default(0)->comment('Total number of agents records attached to tenant');



            $table = $this->procurementStats($table);

            $table->unsignedSmallInteger('number_workshops')->default(0);


            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_procurement_stats');
    }
};
