<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 08 Apr 2024 10:50:24 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Stubs\Migrations\HasSalesStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasSalesStats;
    public function up(): void
    {
        Schema::create('outer_sales_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('outer_id')->index();
            $table->foreign('outer_id')->references('id')->on('outers');
            $table=$this->salesStats($table, ['shop_amount','org_amount','group_amount']);
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('outer_sales_stats');
    }
};
