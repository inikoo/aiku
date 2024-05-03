<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 16 Jan 2024 00:44:51 Malaysia Time, Kuala Lumpur, Malaysia
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
        Schema::create('shop_sales_dashboards', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('shop_id');
            $table->foreign('shop_id')->references('id')->on('shops')->onUpdate('cascade')->onDelete('cascade');

            $table=$this->salesStats($table, ['shop_amount','org_amount','group_amount']);

            $table->timestampsTz();
            $table->unique(['shop_id', 'currency_id']);
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('shop_sales_stats');
    }
};
