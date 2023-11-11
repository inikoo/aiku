<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 21 Apr 2023 13:18:12 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Enums\Procurement\SupplierDelivery\SupplierDeliveryStateEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('supplier_delivery_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('supplier_delivery_id')->index();
            $table->foreign('supplier_delivery_id')->references('id')->on('supplier_deliveries');
            $table->unsignedInteger('supplier_product_id')->index();
            $table->foreign('supplier_product_id')->references('id')->on('supplier_products');
            $table->string('state')->index()->default(SupplierDeliveryStateEnum::CREATING->value);
            $table->dateTimeTz('checked_at')->nullable();
            $table->jsonb('data');
            $table->decimal('unit_quantity');
            $table->decimal('unit_quantity_checked')->default(0);
            $table->decimal('unit_price');
            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_delivery_items');
    }
};
