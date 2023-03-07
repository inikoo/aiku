<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 28 Feb 2023 23:43:02 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use App\Models\Traits\Stubs\HasPaymentStats;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasPaymentStats;

    public function up()
    {
        Schema::create('shop_accounting_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('shop_id')->index();
            $table->foreign('shop_id')->references('id')->on('shops');

            $table->unsignedSmallInteger('number_payment_service_providers')->default(0);
            $table->unsignedSmallInteger('number_payment_accounts')->default(0);

            $table = $this->paymentStats($table);



            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('shop_accounting_stats');
    }
};
