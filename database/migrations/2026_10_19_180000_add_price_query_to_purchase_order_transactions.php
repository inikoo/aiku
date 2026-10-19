<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('purchase_order_transactions', function (Blueprint $table) {
            $table->string('price_query_state')->nullable()->comment('Agent asked the organisation about a new supplier price: pending, accepted, rejected, adjusted');
            $table->decimal('price_query_unit_cost', 18, 6)->nullable()->comment('Unit price the supplier asks for');
            $table->decimal('price_query_previous_unit_cost', 18, 6)->nullable()->comment('Line unit price when the agent asked');
            $table->text('price_query_note')->nullable();
            $table->unsignedSmallInteger('price_queried_by_id')->nullable();
            $table->foreign('price_queried_by_id')->references('id')->on('users')->nullOnDelete();
            $table->dateTimeTz('price_queried_at')->nullable();
            $table->dateTimeTz('price_query_answered_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_transactions', function (Blueprint $table) {
            $table->dropForeign(['price_queried_by_id']);
            $table->dropColumn(['price_query_state', 'price_query_unit_cost', 'price_query_previous_unit_cost', 'price_query_note', 'price_queried_by_id', 'price_queried_at', 'price_query_answered_at']);
        });
    }
};
