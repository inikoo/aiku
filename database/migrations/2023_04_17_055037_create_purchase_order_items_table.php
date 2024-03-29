<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 21 Apr 2023 13:18:07 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Enums\Procurement\PurchaseOrderItem\PurchaseOrderItemStateEnum;
use App\Enums\Procurement\PurchaseOrderItem\PurchaseOrderItemStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('purchase_order_id')->index();
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders');
            $table->unsignedInteger('supplier_product_id')->index();
            $table->foreign('supplier_product_id')->references('id')->on('supplier_products');

            $table->string('state')->index()->default(PurchaseOrderItemStateEnum::CREATING->value);
            $table->string('status')->index()->default(PurchaseOrderItemStatusEnum::PROCESSING->value);

            $table->jsonb('data');
            $table->decimal('unit_quantity');
            $table->decimal('unit_price');
            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};
