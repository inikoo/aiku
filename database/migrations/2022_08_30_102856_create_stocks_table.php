<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Nov 2023 23:03:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Enums\Inventory\Stock\StockStateEnum;
use App\Enums\Inventory\Stock\StockTradeUnitCompositionEnum;
use App\Stubs\Migrations\HasAssetCodeDescription;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasAssetCodeDescription;
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('group_id');
            $table->foreign('group_id')->references('id')->on('groups')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedSmallInteger('organisation_id')->nullable();
            $table->foreign('organisation_id')->references('id')->on('organisations')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('customer_id')->index()->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->onUpdate('cascade')->onDelete('cascade');
            $table->string('slug')->unique()->collation('und_ns');
            $table = $this->assertCodeDescription($table);
            $table->unsignedSmallInteger('stock_family_id')->index()->nullable();
            $table->foreign('stock_family_id')->references('id')->on('stock_families');
            $table->string('trade_unit_composition')->default(StockTradeUnitCompositionEnum::MATCH->value)->nullable();
            $table->string('state')->default(StockStateEnum::IN_PROCESS->value)->index();
            $table->boolean('sellable')->default(1)->index();
            $table->boolean('raw_material')->default(0)->index();
            $table->string('barcode')->index()->nullable();
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
            $table->string('source_id')->nullable()->unique();
        });
        DB::statement('CREATE INDEX ON stocks USING gin (name gin_trgm_ops) ');

    }


    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
