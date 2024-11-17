<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 17 Nov 2024 14:22:02 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('customer_poll_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('customer_poll_id')->index();
            $table->foreign('customer_poll_id')->references('id')->on('customer_polls');
            $table->integer('number_customers')->default(0);
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('customer_poll_stats');
    }
};
