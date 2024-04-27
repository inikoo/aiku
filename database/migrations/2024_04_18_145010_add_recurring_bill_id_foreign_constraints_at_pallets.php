<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 09:16:34 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('pallets', function (Blueprint $table) {
            $table->foreign('current_recurring_bill_id')->references('id')->on('recurring_bills');
        });
    }


    public function down(): void
    {
        Schema::table('pallets', function (Blueprint $table) {
            $table->dropForeign('current_recurring_bill_id_foreign');
        });
    }
};
