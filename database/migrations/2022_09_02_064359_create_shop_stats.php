<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 14:44:17 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use App\Stubs\Migrations\HasCatalogueStats;
use App\Stubs\Migrations\HasCreditsStats;
use App\Stubs\Migrations\HasOrderingStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasCatalogueStats;
    use HasCreditsStats;
    use HasOrderingStats;

    public function up(): void
    {
        Schema::create('shop_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('shop_id')->index();
            $table->foreign('shop_id')->references('id')->on('shops');
            $table = $this->catalogueStats($table);
            $table = $this->billableFields($table);
            $table =$this->getCreditTransactionsStats($table);
            $table =$this->getTopUpsStats($table);



            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('shop_stats');
    }
};
