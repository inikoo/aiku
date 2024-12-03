<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 03 Dec 2024 20:23:36 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Stubs\Migrations\HasInventoryStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasInventoryStats;

    public function up(): void
    {
        Schema::create('group_goods_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('group_id');
            $table->foreign('group_id')->references('id')->on('groups')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('number_trade_units')->default(0);
            $table = $this->goodsStatsFields($table);
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('group_goods_stats');
    }
};
