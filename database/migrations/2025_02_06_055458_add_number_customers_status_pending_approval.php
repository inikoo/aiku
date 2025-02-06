<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('fulfilment_stats', function (Blueprint $table) {
            $table->unsignedInteger('number_customers_status_pending_approval')->default(0);
        });
        Schema::table('group_fulfilment_stats', function (Blueprint $table) {
            $table->unsignedInteger('number_customers_status_pending_approval')->default(0);
        });
        Schema::table('organisation_fulfilment_stats', function (Blueprint $table) {
            $table->unsignedInteger('number_customers_status_pending_approval')->default(0);
        });
    }


    public function down(): void
    {
        Schema::table('fulfilment_stats', function (Blueprint $table) {
            $table->dropColumn('number_customers_status_pending_approval');
        });
        Schema::table('group_fulfilment_stats', function (Blueprint $table) {
            $table->dropColumn('number_customers_status_pending_approval');
        });
        Schema::table('organisation_fulfilment_stats', function (Blueprint $table) {
            $table->dropColumn('number_customers_status_pending_approval');
        });
    }
};
