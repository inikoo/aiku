<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 14:44:17 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

use App\Stubs\Migrations\HasCRMStats;
use App\Stubs\Migrations\HasProspectStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasCRMStats;
    use HasProspectStats;
    public function up(): void
    {
        Schema::create('shop_crm_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('shop_id')->index();
            $table->foreign('shop_id')->references('id')->on('shops');
            $table = $this->crmStats($table);
            $this->prospectsStats($table);
            $table->unsignedSmallInteger('number_prospect_queries')->default(0);
            $table->unsignedSmallInteger('number_customer_queries')->default(0);
            $table->unsignedSmallInteger('number_surveys')->default(0);
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('shop_crm_stats');
    }
};
