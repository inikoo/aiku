<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 25 Oct 2022 12:26:48 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('tenant_procurement_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained();

            $table->unsignedMediumInteger('number_suppliers')->default(0);
            $table->unsignedMediumInteger('number_active_suppliers')->default(0);


            $table->unsignedMediumInteger('number_agents')->default(0);
            $table->unsignedMediumInteger('number_active_agents')->default(0);
            $table->unsignedMediumInteger('number_active_tenant_agents')->default(0);
            $table->unsignedMediumInteger('number_active_global_agents')->default(0);



            $table->unsignedBigInteger('number_purchase_orders')->default(0);
            $purchaseOrderStates = ['in-process', 'submitted', 'confirmed', 'dispatched', 'delivered', 'cancelled'];
            foreach ($purchaseOrderStates as $purchaseOrderState) {
                $table->unsignedBigInteger('number_purchase_orders_state_'.str_replace('-', '_', $purchaseOrderState))->default(0);
            }

            $table->unsignedBigInteger('number_deliveries')->default(0);

            $table->unsignedSmallInteger('number_workshops')->default(0);


            $table->timestampsTz();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tenant_procurement_stats');
    }
};
