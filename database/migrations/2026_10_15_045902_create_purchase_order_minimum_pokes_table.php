<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 12:59:02 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('purchase_order_minimum_pokes', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('group_id')->index();
            $table->foreign('group_id')->references('id')->on('groups');
            $table->unsignedSmallInteger('organisation_id')->comment('organisation asking');
            $table->foreign('organisation_id')->references('id')->on('organisations');
            $table->unsignedSmallInteger('sender_id');
            $table->foreign('sender_id')->references('id')->on('users');
            $table->unsignedSmallInteger('recipient_id');
            $table->foreign('recipient_id')->references('id')->on('users');
            $table->unsignedInteger('purchase_order_id')->comment('draft of the recipient');
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders');
            $table->unsignedInteger('supplier_product_id');
            $table->foreign('supplier_product_id')->references('id')->on('supplier_products');
            $table->decimal('cartons_short', 12, 2);
            $table->unsignedInteger('minimum_carton_order');
            $table->timestampTz('digested_at')->nullable()->comment('sent by email or Slack in a digest');
            $table->timestampsTz();
            $table->index(['recipient_id', 'digested_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_minimum_pokes');
    }
};
