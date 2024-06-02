<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 01 Jun 2024 23:27:11 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use App\Stubs\Migrations\HasSalesIntervals;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasSalesIntervals;

    public function up(): void
    {
        Schema::create('subscription_sales_intervals', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('subscription_id')->index();
            $table->foreign('subscription_id')->references('id')->on('subscriptions');
            $table=$this->salesIntervalFields($table, ['shop_amount', 'org_amount', 'group_amount']);
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('subscription_sales_intervals');
    }
};
