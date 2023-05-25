<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 29 Aug 2022 12:29:04 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('customer_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('customer_id')->index();
            $table->foreign('customer_id')->references('id')->on('customers');

            $table->unsignedInteger('number_web_users')->default(0);
            $table->unsignedInteger('number_active_web_users')->default(0);

            $table->timestampTz('last_submitted_order_at')->nullable();
            $table->timestampTz('last_dispatched_delivery_at')->nullable();
            $table->timestampTz('last_invoiced_at')->nullable();
            $table->unsignedInteger('number_deliveries')->default(0);
            $table->unsignedInteger('number_deliveries_type_order')->default(0);
            $table->unsignedInteger('number_deliveries_type_replacement')->default(0);
            $table->unsignedInteger('number_invoices')->default(0);
            $table->unsignedInteger('number_invoices_type_invoice')->default(0);
            $table->unsignedInteger('number_invoices_type_refund')->default(0);
            $table->unsignedInteger('number_clients')->default(0);
            $table->unsignedInteger('number_active_clients')->default(0);
            $table->unsignedInteger('number_stored_items')->default(0);

            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('customer_stats');
    }
};
