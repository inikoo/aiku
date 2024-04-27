<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 18 Apr 2024 22:50:21 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('fulfilment_customers', function (Blueprint $table) {
            $table->foreign('current_recurring_bill_id')->references('id')->on('recurring_bills');
        });
    }


    public function down(): void
    {
        Schema::table('fulfilment_customers', function (Blueprint $table) {
            $table->dropForeign('current_recurring_bill_id_foreign');
        });
    }
};
