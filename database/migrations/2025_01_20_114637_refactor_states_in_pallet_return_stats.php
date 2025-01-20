<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('pallet_return_stats', function (Blueprint $table) {
            $table->renameColumn('number_pallets_state_request_return', 'number_pallets_state_request_return_in_process');
            $table->unsignedInteger('number_pallets_state_request_return_submitted')->default(0);
            $table->unsignedInteger('number_pallets_state_request_return_confirmed')->default(0);
        });

        Schema::table('pallet_delivery_stats', function (Blueprint $table) {
            $table->renameColumn('number_pallets_state_request_return', 'number_pallets_state_request_return_in_process');
            $table->unsignedInteger('number_pallets_state_request_return_submitted')->default(0);
            $table->unsignedInteger('number_pallets_state_request_return_confirmed')->default(0);
        });

        Schema::table('location_stats', function (Blueprint $table) {
            $table->renameColumn('number_pallets_state_request_return', 'number_pallets_state_request_return_in_process');
            $table->unsignedInteger('number_pallets_state_request_return_submitted')->default(0);
            $table->unsignedInteger('number_pallets_state_request_return_confirmed')->default(0);
        });

        Schema::table('warehouse_area_stats', function (Blueprint $table) {
            $table->renameColumn('number_pallets_state_request_return', 'number_pallets_state_request_return_in_process');
            $table->unsignedInteger('number_pallets_state_request_return_submitted')->default(0);
            $table->unsignedInteger('number_pallets_state_request_return_confirmed')->default(0);
        });


    }

    public function down(): void
    {
        Schema::table('pallet_return_stats', function (Blueprint $table) {
            $table->renameColumn('number_pallets_state_request_return_in_process', 'number_pallets_state_request_return');
            $table->dropColumn('number_pallets_state_request_return_submitted');
            $table->dropColumn('number_pallets_state_request_return_confirmed');
        });

        Schema::table('pallet_delivery_stats', function (Blueprint $table) {
            $table->renameColumn('number_pallets_state_request_return_in_process', 'number_pallets_state_request_return');
            $table->dropColumn('number_pallets_state_request_return_submitted');
            $table->dropColumn('number_pallets_state_request_return_confirmed');
        });

        Schema::table('location_stats', function (Blueprint $table) {
            $table->renameColumn('number_pallets_state_request_return_in_process', 'number_pallets_state_request_return');
            $table->dropColumn('number_pallets_state_request_return_submitted');
            $table->dropColumn('number_pallets_state_request_return_confirmed');
        });

        Schema::table('warehouse_area_stats', function (Blueprint $table) {
            $table->renameColumn('number_pallets_state_request_return_in_process', 'number_pallets_state_request_return');
            $table->dropColumn('number_pallets_state_request_return_submitted');
            $table->dropColumn('number_pallets_state_request_return_confirmed');
        });
    }
};
