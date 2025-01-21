<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 21 Jan 2025 18:22:45 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('fulfilment_customers', function (Blueprint $table) {
            $table->renameColumn('number_pallets_state_request_return', 'number_pallets_state_request_return_in_process');
            $table->unsignedInteger('number_pallets_state_request_return_submitted')->default(0);
            $table->unsignedInteger('number_pallets_state_request_return_confirmed')->default(0);
        });

        Schema::table('fulfilment_stats', function (Blueprint $table) {
            $table->renameColumn('number_pallets_state_request_return', 'number_pallets_state_request_return_in_process');
            $table->unsignedInteger('number_pallets_state_request_return_submitted')->default(0);
            $table->unsignedInteger('number_pallets_state_request_return_confirmed')->default(0);
        });

        Schema::table('organisation_fulfilment_stats', function (Blueprint $table) {
            $table->renameColumn('number_pallets_state_request_return', 'number_pallets_state_request_return_in_process');
            $table->unsignedInteger('number_pallets_state_request_return_submitted')->default(0);
            $table->unsignedInteger('number_pallets_state_request_return_confirmed')->default(0);
        });

        Schema::table('group_fulfilment_stats', function (Blueprint $table) {
            $table->renameColumn('number_pallets_state_request_return', 'number_pallets_state_request_return_in_process');
            $table->unsignedInteger('number_pallets_state_request_return_submitted')->default(0);
            $table->unsignedInteger('number_pallets_state_request_return_confirmed')->default(0);
        });

        Schema::table('warehouse_stats', function (Blueprint $table) {
            $table->renameColumn('number_pallets_state_request_return', 'number_pallets_state_request_return_in_process');
            $table->unsignedInteger('number_pallets_state_request_return_submitted')->default(0);
            $table->unsignedInteger('number_pallets_state_request_return_confirmed')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('fulfilment_customers', function (Blueprint $table) {
            $table->renameColumn('number_pallets_state_request_return_in_process', 'number_pallets_state_request_return');
            $table->dropColumn('number_pallets_state_request_return_submitted');
            $table->dropColumn('number_pallets_state_request_return_confirmed');
        });

        Schema::table('fulfilment_stats', function (Blueprint $table) {
            $table->renameColumn('number_pallets_state_request_return_in_process', 'number_pallets_state_request_return');
            $table->dropColumn('number_pallets_state_request_return_submitted');
            $table->dropColumn('number_pallets_state_request_return_confirmed');
        });

        Schema::table('organisation_fulfilment_stats', function (Blueprint $table) {
            $table->renameColumn('number_pallets_state_request_return_in_process', 'number_pallets_state_request_return');
            $table->dropColumn('number_pallets_state_request_return_submitted');
            $table->dropColumn('number_pallets_state_request_return_confirmed');
        });

        Schema::table('group_fulfilment_stats', function (Blueprint $table) {
            $table->renameColumn('number_pallets_state_request_return_in_process', 'number_pallets_state_request_return');
            $table->dropColumn('number_pallets_state_request_return_submitted');
            $table->dropColumn('number_pallets_state_request_return_confirmed');
        });

        Schema::table('warehouse_stats', function (Blueprint $table) {
            $table->renameColumn('number_pallets_state_request_return_in_process', 'number_pallets_state_request_return');
            $table->dropColumn('number_pallets_state_request_return_submitted');
            $table->dropColumn('number_pallets_state_request_return_confirmed');
        });
    }
};
