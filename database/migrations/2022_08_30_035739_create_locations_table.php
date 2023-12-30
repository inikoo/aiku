<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 12:06:44 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

use App\Enums\Inventory\Location\LocationStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug')->unique()->collation('und_ns');
            $table->unsignedSmallInteger('warehouse_id')->index();
            $table->foreign('warehouse_id')->references('id')->on('warehouses');
            $table->unsignedSmallInteger('warehouse_area_id')->nullable()->index();
            $table->foreign('warehouse_area_id')->references('id')->on('warehouse_areas');
            $table->string('status')->index()->default(LocationStatusEnum::OPERATIONAL->value);
            $table->string('code', 64)->index()->collation('und_ns');
            $table->decimal('stock_value', 16)->default(0);
            $table->boolean('is_empty')->default(true);
            $table->double('max_weight')->nullable();
            $table->double('max_volume')->nullable();
            $table->jsonb('data');
            $table->dateTimeTz('audited_at')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();
            $table->string('source_id')->nullable()->unique();
        });

    }


    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
