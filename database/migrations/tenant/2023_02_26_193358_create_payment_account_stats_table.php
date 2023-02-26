<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 27 Feb 2023 10:00:41 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('payment_account_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_account_id')->constrained();
            $table->unsignedSmallInteger('number_payments')->default(0);
            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('payment_account_stats');
    }
};
