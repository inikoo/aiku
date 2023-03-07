<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 03 Mar 2023 23:08:39 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('tenant_procurement_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onUpdate('cascade')->onDelete('cascade');

            $table->unsignedInteger('number_suppliers')->default(0);
            $table->unsignedInteger('number_active_suppliers')->default(0);


            $table->unsignedSmallInteger('number_agents')->default(0);
            $table->unsignedSmallInteger('number_active_agents')->default(0);
            $table->unsignedSmallInteger('number_active_tenant_agents')->default(0);
            $table->unsignedSmallInteger('number_active_global_agents')->default(0);



            $table->unsignedInteger('number_purchase_orders')->default(0);
            $purchaseOrderStates = ['in-process', 'submitted', 'confirmed', 'dispatched', 'delivered', 'cancelled'];
            foreach ($purchaseOrderStates as $purchaseOrderState) {
                $table->unsignedInteger('number_purchase_orders_state_'.str_replace('-', '_', $purchaseOrderState))->default(0);
            }

            $table->unsignedInteger('number_deliveries')->default(0);

            $table->unsignedSmallInteger('number_workshops')->default(0);


            $table->timestampsTz();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tenant_procurement_stats');
    }
};
