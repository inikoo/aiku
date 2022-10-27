<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 25 Oct 2022 15:06:23 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('supplier_products', function (Blueprint $table) {
            $table->id();
            $table->enum('composition', ['unit', 'multiple', 'mix'])->default('unit');

            $table->string('slug')->nullable()->index();


            $table->unsignedMediumInteger('supplier_id')->nullable();
            $table->foreign('supplier_id')->references('id')->on('suppliers');

            $table->unsignedMediumInteger('sub_supplier_id')->nullable();
            $table->foreign('sub_supplier_id')->references('id')->on('suppliers');


            $table->enum('state', ['creating', 'active', 'no-available', 'discontinuing', 'discontinued'])->nullable()->index();
            $table->boolean('status')->nullable()->index();

            $table->enum('stock_quantity_status', ['surplus', 'optimal', 'low', 'critical', 'out-of-stock', 'no-applicable'])->default('no-applicable')->nullable()->index();


            $table->string('code')->index();
            $table->string('name', 255)->nullable();
            $table->text('description')->nullable();

            $table->unsignedDecimal('cost', 18, 4)->comment('unit cost');
            $table->unsignedMediumInteger('pack')->nullable()->comment('units per pack');
            $table->unsignedMediumInteger('outer')->nullable()->comment('units per outer');
            $table->unsignedMediumInteger('carton')->nullable()->comment('units per carton');


            $table->jsonb('settings');
            $table->jsonb('shared_data');
            $table->jsonb('tenant_data');

            $table->timestampsTz();
            $table->softDeletesTz();
            $table->uuid('global_id')->index()->nullable();
            $table->unsignedBigInteger('source_id')->nullable()->unique();
        });
    }


    public function down()
    {
        Schema::dropIfExists('supplier_products');
    }
};
