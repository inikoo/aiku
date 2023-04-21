<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->increments('id');
//
//            $table->unsignedSmallInteger('group_id');
//            $table->foreign('group_id')->references('id')->on('groups');
//            $table->unsignedSmallInteger('tenant_id');
//            $table->foreign('tenant_id')->references('id')->on('tenants');

            $table->unsignedInteger('purchase_order_id')->index();
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders');
            $table->unsignedInteger('supplier_product_id')->index();
            $table->foreign('supplier_product_id')->references('id')->on('supplier_products');
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
