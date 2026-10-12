<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 12 Oct 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('job_orders', function (Blueprint $table) {
            $table->date('planned_for')->nullable()->index();
            $table->unsignedSmallInteger('plan_position')->nullable();
        });
        Schema::table('job_order_item_tasks', function (Blueprint $table) {
            $table->unsignedSmallInteger('employee_id')->nullable()->index();
            $table->foreign('employee_id')->references('id')->on('employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('job_order_item_tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('employee_id');
        });
        Schema::table('job_orders', function (Blueprint $table) {
            $table->dropColumn(['planned_for', 'plan_position']);
        });
    }
};
