<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('purchase_order_chases', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('group_id')->index();
            $table->foreign('group_id')->references('id')->on('groups');
            $table->unsignedSmallInteger('organisation_id')->index();
            $table->foreign('organisation_id')->references('id')->on('organisations');
            $table->unsignedInteger('purchase_order_id');
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->cascadeOnDelete();
            $table->unsignedSmallInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->timestampTz('chased_at');
            $table->string('channel');
            $table->string('contact')->nullable();
            $table->date('promised_at')->nullable();
            $table->text('note')->nullable();
            $table->timestampsTz();
            $table->index(['purchase_order_id', 'chased_at']);
        });

        Schema::create('supplier_change_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('group_id')->index();
            $table->foreign('group_id')->references('id')->on('groups');
            $table->unsignedSmallInteger('agent_id')->nullable()->index();
            $table->foreign('agent_id')->references('id')->on('agents');
            $table->string('type')->index();
            $table->string('state')->default('pending')->index();
            $table->unsignedInteger('supplier_id')->index();
            $table->foreign('supplier_id')->references('id')->on('suppliers');
            $table->unsignedInteger('target_supplier_id')->nullable();
            $table->foreign('target_supplier_id')->references('id')->on('suppliers');
            $table->text('reason');
            $table->unsignedSmallInteger('requested_by_user_id')->nullable();
            $table->foreign('requested_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->unsignedSmallInteger('decided_by_user_id')->nullable();
            $table->foreign('decided_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->timestampTz('decided_at')->nullable();
            $table->text('decision_note')->nullable();
            $table->jsonb('data')->default('{}');
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_change_requests');
        Schema::dropIfExists('purchase_order_chases');
    }
};
