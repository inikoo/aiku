<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('agent_warehouse_stocks', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('group_id')->index();
            $table->foreign('group_id')->references('id')->on('groups');
            $table->unsignedSmallInteger('agent_id')->index();
            $table->foreign('agent_id')->references('id')->on('agents');
            $table->unsignedSmallInteger('organisation_id')->index();
            $table->foreign('organisation_id')->references('id')->on('organisations');
            $table->unsignedInteger('supplier_product_id')->index();
            $table->foreign('supplier_product_id')->references('id')->on('supplier_products');
            $table->unsignedInteger('purchase_order_id')->nullable()->index();
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->nullOnDelete();
            $table->string('state')->index();
            $table->decimal('quantity', 16, 3);
            $table->text('notes')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_warehouse_stocks');
    }
};
