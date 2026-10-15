<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('productions', function (Blueprint $table) {
            $table->unsignedInteger('factory_location_id')->nullable()->index();
            $table->foreign('factory_location_id')->references('id')->on('locations')->nullOnDelete();
        });

        Schema::create('production_material_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('group_id')->index();
            $table->foreign('group_id')->references('id')->on('groups')->cascadeOnDelete();
            $table->unsignedSmallInteger('organisation_id')->index();
            $table->foreign('organisation_id')->references('id')->on('organisations')->cascadeOnDelete();
            $table->unsignedSmallInteger('production_id')->index();
            $table->foreign('production_id')->references('id')->on('productions')->cascadeOnDelete();
            $table->unsignedSmallInteger('warehouse_id')->index();
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->cascadeOnDelete();
            $table->date('plan_date')->index();
            $table->string('state')->index();
            $table->unsignedSmallInteger('checker_employee_id')->nullable()->index();
            $table->foreign('checker_employee_id')->references('id')->on('employees')->nullOnDelete();
            $table->unsignedSmallInteger('submitted_by_user_id')->nullable();
            $table->foreign('submitted_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->dateTimeTz('submitted_at')->nullable();
            $table->dateTimeTz('delivered_at')->nullable();
            $table->timestampsTz();
        });

        Schema::create('production_material_request_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('production_material_request_id')->index();
            $table->foreign('production_material_request_id')->references('id')->on('production_material_requests')->cascadeOnDelete();
            $table->unsignedInteger('raw_material_id')->index();
            $table->foreign('raw_material_id')->references('id')->on('raw_materials')->cascadeOnDelete();
            $table->unsignedInteger('org_stock_id')->index();
            $table->foreign('org_stock_id')->references('id')->on('org_stocks')->cascadeOnDelete();
            $table->decimal('needed_units', 16, 3);
            $table->decimal('missing_units', 16, 3);
            $table->decimal('box_units', 16, 3);
            $table->unsignedInteger('boxes');
            $table->decimal('skos_to_bring', 16, 3);
            $table->jsonb('job_orders');
            $table->string('state')->index();
            $table->decimal('delivered_skos', 16, 3)->default(0);
            $table->unsignedSmallInteger('delivered_by_user_id')->nullable();
            $table->foreign('delivered_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->dateTimeTz('delivered_at')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_material_request_items');
        Schema::dropIfExists('production_material_requests');
        Schema::table('productions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('factory_location_id');
        });
    }
};
