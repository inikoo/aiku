<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 28 Apr 2024 12:51:50 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('recurring_bill_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('recurring_bill_id')->index();
            $table->foreign('recurring_bill_id')->references('id')->on('recurring_bills');
            $table->unsignedSmallInteger('number_transactions')->default(0);
            $table->unsignedSmallInteger('number_transactions_type_pallets')->default(0);
            $table->unsignedSmallInteger('number_transactions_type_stored_items')->default(0);
            $table->timestampsTz();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('recurring_bill_stats');
    }
};
