<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 23 Jan 2024 08:52:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Enums\Inventory\Stock\StockStateEnum;
use App\Stubs\Migrations\HasAssetCodeDescription;
use App\Stubs\Migrations\HasGroupOrganisationRelationship;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasAssetCodeDescription;
    use HasGroupOrganisationRelationship;

    public function up(): void
    {
        Schema::create('org_stocks', function (Blueprint $table) {
            $table->increments('id');
            $table = $this->groupOrgRelationship($table);
            $table->unsignedInteger('stock_id')->index()->nullable();
            $table->foreign('stock_id')->references('id')->on('stocks')->onUpdate('cascade')->onDelete('cascade');

            $table->unsignedInteger('customer_id')->index()->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->onUpdate('cascade')->onDelete('cascade');
            $table->string('slug')->unique()->collation('und_ns');

            $table->string('org_state')->default(StockStateEnum::IN_PROCESS->value)->index();
            $table->boolean('org_sellable')->default(1)->index();
            $table->boolean('org_raw_material')->default(0)->index();
            $table->string('barcode')->index()->nullable();
            $table->decimal('quantity_in_locations', 16, 3)->nullable()->default(0)->comment('stock quantity in units');
            $table->string('quantity_status')->nullable()->index();
            $table->float('available_forecast')->nullable()->comment('days');
            $table->unsignedSmallInteger('number_locations')->default(0);

            $table->decimal('value_in_locations', 16)->default(0);

            $table->timestampsTz();
            $table->dateTimeTz('org_activated_at')->nullable();
            $table->dateTimeTz('org_discontinuing_at')->nullable();
            $table->dateTimeTz('org_discontinued_at')->nullable();
            $table->softDeletesTz();
            $table->string('source_id')->nullable()->unique();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('org_stocks');
    }
};
