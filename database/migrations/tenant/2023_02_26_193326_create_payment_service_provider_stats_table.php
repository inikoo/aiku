<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 09:57:41 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('payment_service_provider_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_service_provider_id')->constrained();
            $table->unsignedSmallInteger('number_accounts')->default(0);
            $table->unsignedSmallInteger('number_payments')->default(0);
            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('payment_service_provider_stats');
    }
};
