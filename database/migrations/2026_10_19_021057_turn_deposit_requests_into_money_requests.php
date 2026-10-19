<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->decimal('advance_percentage', 5)->nullable();
        });

        Schema::table('deposit_requests', function (Blueprint $table) {
            $table->unsignedSmallInteger('organisation_id')->nullable()->index();
            $table->foreign('organisation_id')->references('id')->on('organisations');
            $table->text('notes')->nullable();
        });

        Schema::table('deposit_request_items', function (Blueprint $table) {
            $table->unsignedInteger('aspo_deposit_id')->nullable()->change();
            $table->string('kind')->default('advance');
            $table->unsignedInteger('purchase_order_id')->nullable()->index();
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders');
        });

        Schema::table('agent_payments', function (Blueprint $table) {
            $table->unsignedInteger('deposit_request_id')->nullable()->index();
            $table->foreign('deposit_request_id')->references('id')->on('deposit_requests');
        });
    }

    public function down(): void
    {
        Schema::table('agent_payments', function (Blueprint $table) {
            $table->dropForeign(['deposit_request_id']);
            $table->dropColumn('deposit_request_id');
        });

        Schema::table('deposit_request_items', function (Blueprint $table) {
            $table->dropForeign(['purchase_order_id']);
            $table->dropColumn(['kind', 'purchase_order_id']);
            $table->unsignedInteger('aspo_deposit_id')->nullable(false)->change();
        });

        Schema::table('deposit_requests', function (Blueprint $table) {
            $table->dropForeign(['organisation_id']);
            $table->dropColumn(['organisation_id', 'notes']);
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('advance_percentage');
        });
    }
};
