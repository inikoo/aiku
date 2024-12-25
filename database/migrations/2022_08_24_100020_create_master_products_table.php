<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 25 Dec 2024 02:19:29 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('master_products', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedSmallInteger('group_id')->index();
            $table->foreign('group_id')->references('id')->on('groups')->nullOnDelete();
            $table->unsignedInteger('master_family_id')->nullable();
            $table->foreign('master_family_id')->references('id')->on('master_product_categories')->nullOnDelete();
            $table->unsignedInteger('master_sub_department_id')->nullable();
            $table->foreign('master_sub_department_id')->references('id')->on('master_product_categories')->nullOnDelete();
            $table->unsignedInteger('master_department_id')->nullable();
            $table->foreign('master_department_id')->references('id')->on('master_product_categories')->nullOnDelete();

            $table->boolean('is_main')->default(true)->index();
            $table->boolean('status')->index()->default(true);
            $table->string('slug')->unique()->collation('und_ns');
            $table->string('code')->index()->collation('und_ns');
            $table->string('name', 255)->nullable();
            $table->text('description')->nullable()->fulltext();
            $table->string('unit');
            $table->decimal('price', 18)->nullable();
            $table->jsonb('data');
            $table->jsonb('settings');

            $table->unsignedInteger('gross_weight')->nullable()->comment('outer weight including packing, grams');
            $table->unsignedInteger('marketing_weight')->nullable()->comment('to be shown in website, grams');

            $table->string('barcode')->index()->nullable()->comment('mirror from trade_unit');
            $table->decimal('rrp', 12, 3)->nullable()->comment('RRP per outer');
            $table->unsignedInteger('image_id')->nullable();
            $table->unsignedInteger('available_quantity')->default(0)->nullable()->comment('outer available quantity for sale');

            $table->decimal('variant_ratio', 9, 3)->default(1);
            $table->boolean('variant_is_visible')->default(true)->index();
            $table->unsignedInteger('main_master_product_id')->nullable()->index();

            $table->timestampsTz();
            $table->softDeletesTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('master_products');
    }
};
