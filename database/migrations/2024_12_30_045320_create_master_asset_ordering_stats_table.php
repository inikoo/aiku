<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 30 Dec 2024 12:53:50 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Stubs\Migrations\HasOrderingStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasOrderingStats;
    public function up(): void
    {
        Schema::create('master_asset_ordering_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('master_asset_id')->index();
            $table->foreign('master_asset_id')->references('id')->on('master_assets')->onDelete('cascade')->onUpdate('cascade');
            $table = $this->orderingStatsFields($table);
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('master_asset_ordering_stats');
    }
};
