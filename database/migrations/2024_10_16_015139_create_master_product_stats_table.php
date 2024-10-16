<?php

use App\Enums\Catalogue\Product\ProductStateEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('master_product_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('master_product_id')->index();
            $table->foreign('master_product_id')->references('id')->on('master_products');

            foreach (ProductStateEnum::cases() as $case) {
                $table->unsignedInteger('number_master_products_state_'.$case->snake())->default(0);
            }
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('master_product_stats');
    }
};
