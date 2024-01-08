<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 29 Nov 2023 21:58:57 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Stubs\Migrations\HasInventoryStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasInventoryStats;
    public function up(): void
    {
        Schema::create('group_inventory_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('group_id');
            $table->foreign('group_id')->references('id')->on('groups')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('number_stock_families')->default(0);

            foreach (StockFamilyStateEnum::cases() as $stockFamilyState) {
                $table->unsignedInteger('number_stock_families_state_'.$stockFamilyState->snake())->default(0);
            }

            $table->unsignedInteger('number_stocks')->default(0);
            foreach (StockStateEnum::cases() as $stockState) {
                $table->unsignedInteger('number_stocks_state_'.$stockState->snake())->default(0);
            }
            foreach (StockQuantityStatusEnum::cases() as $stockQuantityStatus) {
                $table->unsignedInteger('number_stocks_quantity_status_'.$stockQuantityStatus->snake())->default(0);
            }
            $table= $this->inventoryStats($table);
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('group_inventory_stats');
    }
};
