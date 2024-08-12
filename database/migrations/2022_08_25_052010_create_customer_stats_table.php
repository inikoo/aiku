<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 29 Aug 2022 12:29:04 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

use App\Stubs\Migrations\HasCreditsStats;
use App\Stubs\Migrations\HasSalesStats;
use App\Stubs\Migrations\HasWebStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasSalesStats;
    use HasWebStats;
    use HasCreditsStats;

    public function up(): void
    {
        Schema::create('customer_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('customer_id')->index();
            $table->foreign('customer_id')->references('id')->on('customers');
            $table=$this->salesStatsFields($table);
            $table=$this->getWebUsersStatsFields($table);
            $table->unsignedInteger('number_clients')->default(0);
            $table->unsignedInteger('number_current_clients')->default(0);
            $table=$this->getCreditTransactionsStats($table);
            $table=$this->getTopUpsStats($table);

            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('customer_stats');
    }
};
