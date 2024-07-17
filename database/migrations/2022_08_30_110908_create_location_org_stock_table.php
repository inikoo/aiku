<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 19:10:28 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use App\Enums\Inventory\LocationStock\LocationStockTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('location_org_stock', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('org_stock_id')->index();
            $table->foreign('org_stock_id')->references('id')->on('org_stocks');
            $table->unsignedInteger('location_id')->index();
            $table->foreign('location_id')->references('id')->on('locations');
            $table->decimal('quantity', 16, 3)->default(0)->comment('in units');
            $table->decimal('value', 12)->default(0)->comment('total value based in cost');
            $table->decimal('commercial_value', 12)->default(0)->comment('total value based selling price');
            $table->string('type')->index()->default(LocationStockTypeEnum::PICKING->value);
            $table->smallInteger('picking_priority')->nullable()->index();
            $table->string('notes')->nullable();
            $table->jsonb('data');
            $table->jsonb('settings');
            $table->dateTimeTz('audited_at')->nullable()->index();
            $table->timestampsTz();
            $table->unsignedInteger('source_stock_id')->nullable();
            $table->unsignedInteger('source_location_id')->nullable();
            $table->boolean('dropshipping_pipe')->default(false)->index();
            $table->unique(['org_stock_id', 'location_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('location_org_stock');
    }
};
