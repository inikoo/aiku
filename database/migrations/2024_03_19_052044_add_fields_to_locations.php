<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Mar 2024 13:26:34 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->boolean('allow_stocks')->default(true);
            $table->boolean('allow_dropshipping')->default(true);
            $table->boolean('allow_fulfilment')->default(true);
            $table->boolean('has_stock_slots')->default(false);
            $table->boolean('has_dropshipping_slots')->default(false);
            $table->boolean('has_fulfilment')->default(false);

        });
    }


    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn('allow_stocks');
            $table->dropColumn('allow_fulfilment');
            $table->dropColumn('allow_dropshipping');
            $table->dropColumn('has_stock_slots');
            $table->dropColumn('has_fulfilment');
            $table->dropColumn('has_dropshipping_slots');
        });
    }
};
