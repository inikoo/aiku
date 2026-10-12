<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('stock_delivery_costs', function (Blueprint $table) {
            $table->boolean('is_waiting_for_invoice')->default(false);
            $table->date('invoice_expected_at')->nullable();
            $table->boolean('is_estimate')->default(false);
            $table->string('category')->nullable()->index();
            $table->softDeletes();
        });

        DB::statement("create unique index stock_delivery_costs_one_per_type on stock_delivery_costs (stock_delivery_id, type) where type <> 'extra' and deleted_at is null");
    }

    public function down(): void
    {
        DB::statement('drop index if exists stock_delivery_costs_one_per_type');

        Schema::table('stock_delivery_costs', function (Blueprint $table) {
            $table->dropColumn(['is_waiting_for_invoice', 'invoice_expected_at', 'is_estimate', 'category', 'deleted_at']);
        });
    }
};
