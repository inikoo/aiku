<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 28 Mar 2023 23:59:36 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('paymentables', function (Blueprint $table) {
            $table->unsignedInteger('payment_id');
            $table->foreign('payment_id')->references('id')->on('payments');
            $table->string('paymentable_type');
            $table->unsignedInteger('paymentable_id');
            $table->decimal('amount', 12);
            $table->float('share')->default(1);
            $table->timestampsTz();
            $table->unique(['payment_id','paymentable_type','paymentable_id']);
        });
    }


    public function down()
    {
        Schema::dropIfExists('paymentables');
    }
};
