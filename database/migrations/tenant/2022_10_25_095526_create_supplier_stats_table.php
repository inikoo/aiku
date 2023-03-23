<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 25 Oct 2022 10:58:51 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


use App\Stubs\Migrations\HasProcurementStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasProcurementStats;

    public function up()
    {
        Schema::create('supplier_stats', function (Blueprint $table) {
            $table->mediumIncrements('id');
            $table->unsignedInteger('supplier_id')->index();
            $table->foreign('supplier_id')->references('id')->on('suppliers');
            $table = $this->procurementStats($table);
            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('supplier_stats');
    }
};
