<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 24 Oct 2022 09:54:15 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use App\Enums\Inventory\Stock\StockQuantityStatusEnum;
use App\Enums\Inventory\Stock\StockStateEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('stock_family_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('stock_family_id')->index();
            $table->foreign('stock_family_id')->references('id')->on('stock_families');
            $table->unsignedInteger('number_stocks')->default(0);
            foreach (StockStateEnum::cases() as $stockState) {
                $table->unsignedInteger('number_stocks_state_'.$stockState->snake())->default(0);
            }
            foreach (StockQuantityStatusEnum::cases() as $quantityStatus) {
                $table->unsignedInteger('number_stocks_quantity_status_'.$quantityStatus->snake())->default(0);
            }
            $table->softDeletesTz();
            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('stock_family_stats');
    }
};
