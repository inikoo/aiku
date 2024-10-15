<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('master_shop_has_master_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('master_shop_id')->index();
            $table->foreign('master_shop_id')->references('id')->on('master_shops')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedSmallInteger('master_product_id')->index();
            $table->foreign('master_product_id')->references('id')->on('master_products')->onUpdate('cascade')->onDelete('cascade');
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('master_shop_has_master_products');
    }
};
