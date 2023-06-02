<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 18:36:08 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use App\Enums\Inventory\Stock\StockStateEnum;
use App\Enums\Inventory\Stock\StockTradeUnitCompositionEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug')->unique();
            $table->string('code')->index();
            $table->string('owner_type')->comment('Tenant|Customer');
            $table->unsignedInteger('owner_id');
            $table->index([
                'owner_type',
                'owner_id'
            ]);
            $table->unsignedSmallInteger('stock_family_id')->index()->nullable();
            $table->foreign('stock_family_id')->references('id')->on('stock_families');

            $table->string('trade_unit_composition')->default(StockTradeUnitCompositionEnum::MATCH->value)->nullable();
            $table->string('state')->default(StockStateEnum::IN_PROCESS->value)->index();
            $table->boolean('sellable')->default(1)->index();
            $table->boolean('raw_material')->default(0)->index();

            $table->string('barcode')->index()->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('units_per_pack')->nullable()->comment('units per pack');
            $table->unsignedInteger('units_per_carton')->nullable()->comment('units per carton');
            $table->decimal('quantity_in_locations', 16, 3)->nullable()->default(0)->comment('stock quantity in units');
            $table->string('quantity_status')->nullable()->index();
            $table->float('available_forecast')->nullable()->comment('days');
            $table->unsignedSmallInteger('number_locations')->default(0);
            $table->decimal('unit_value', 16)->nullable();
            $table->decimal('value_in_locations', 16)->default(0);

            $table->unsignedBigInteger('image_id')->nullable();
            $table->jsonb('settings');
            $table->jsonb('data');
            $table->timestampsTz();
            $table->dateTimeTz('activated_at')->nullable();
            $table->dateTimeTz('discontinuing_at')->nullable();
            $table->dateTimeTz('discontinued_at')->nullable();
            $table->softDeletesTz();
            $table->unsignedInteger('source_id')->nullable()->unique();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
