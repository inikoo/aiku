<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 23 Oct 2026 Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('purchase_order_transactions', function (Blueprint $table) {
            $table->string('quantity_query_state')->nullable()->comment('Agent asked the organisation to change the quantity ordered: pending, accepted, rejected');
            $table->decimal('quantity_query_quantity', 16, 3)->nullable()->comment('Units the agent asks for');
            $table->decimal('quantity_query_previous_quantity', 16, 3)->nullable()->comment('Units ordered on the line when the agent asked');
            $table->text('quantity_query_note')->nullable();
            $table->unsignedSmallInteger('quantity_queried_by_id')->nullable();
            $table->foreign('quantity_queried_by_id')->references('id')->on('users')->nullOnDelete();
            $table->dateTimeTz('quantity_queried_at')->nullable();
            $table->dateTimeTz('quantity_query_answered_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_transactions', function (Blueprint $table) {
            $table->dropForeign(['quantity_queried_by_id']);
            $table->dropColumn(['quantity_query_state', 'quantity_query_quantity', 'quantity_query_previous_quantity', 'quantity_query_note', 'quantity_queried_by_id', 'quantity_queried_at', 'quantity_query_answered_at']);
        });
    }
};
