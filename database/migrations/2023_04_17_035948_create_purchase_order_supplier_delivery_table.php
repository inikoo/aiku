<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('purchase_order_supplier_deliveries', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('purchase_order_id')->index();
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders');
            $table->unsignedInteger('supplier_delivery_id')->index();
            $table->foreign('supplier_delivery_id')->references('id')->on('supplier_deliveries');
            $table->timestampsTz();
            $table->unique(['purchase_order_id', 'supplier_delivery_id']);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('purchase_order_supplier_deliveries');
    }
};
