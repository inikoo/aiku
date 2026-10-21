<?php

/*
    * Author: Vika Aqordi
    * Created on: 2026-10-20 09:06
    * Github: https://github.com/aqordeon
    * Copyright: 2026
*/

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('packaging_components', function (Blueprint $table) {
            $table->boolean('is_dropdown_item')->default(false)->index();
            $table->string('dropdown_key', 32)->nullable();
            $table->jsonb('dropdown_labels')->nullable();
            $table->unsignedSmallInteger('dropdown_position')->nullable();
            $table->string('default_role', 24)->nullable();
            $table->boolean('is_epr_reportable')->default(true);
            $table->boolean('needs_review')->default(false);
            $table->unique(['group_id', 'dropdown_key']);
        });

        Schema::create('supplier_product_packing_specs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('group_id')->index();
            $table->foreign('group_id')->references('id')->on('groups');
            $table->unsignedInteger('supplier_product_id')->index();
            $table->foreign('supplier_product_id')->references('id')->on('supplier_products')->cascadeOnDelete();
            $table->unsignedSmallInteger('agent_id')->nullable()->index();
            $table->foreign('agent_id')->references('id')->on('agents')->nullOnDelete();
            $table->unsignedSmallInteger('version');
            $table->string('status', 16)->index();
            $table->date('effective_from');
            $table->string('packed_by', 16);
            $table->unsignedInteger('carton_length_mm')->nullable();
            $table->unsignedInteger('carton_width_mm')->nullable();
            $table->unsignedInteger('carton_height_mm')->nullable();
            $table->unsignedInteger('declared_carton_gross_weight_g')->nullable();
            $table->unsignedInteger('measured_carton_gross_weight_g')->nullable();
            $table->decimal('packaging_weight_g', 12, 3)->nullable();
            $table->decimal('weight_check_deviation_pct', 8, 2)->nullable();
            $table->boolean('needs_review')->default(false)->index();
            $table->string('review_reason')->nullable();
            $table->boolean('materials_weighed')->default(false);
            $table->text('return_reason')->nullable();
            $table->timestampTz('returned_at')->nullable();
            $table->unsignedSmallInteger('returned_by_user_id')->nullable();
            $table->foreign('returned_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->unsignedSmallInteger('submitted_by_user_id')->nullable();
            $table->foreign('submitted_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->timestampTz('submitted_at')->nullable();
            $table->unsignedSmallInteger('verified_by_user_id')->nullable();
            $table->foreign('verified_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->timestampTz('verified_at')->nullable();
            $table->unsignedInteger('copied_from_id')->nullable();
            $table->jsonb('data')->default('{}');
            $table->timestampsTz();
            $table->unique(['supplier_product_id', 'version']);
        });

        Schema::table('supplier_product_packing_specs', function (Blueprint $table) {
            $table->foreign('copied_from_id')->references('id')->on('supplier_product_packing_specs')->nullOnDelete();
        });

        Schema::create('packing_spec_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('packing_spec_id')->index();
            $table->foreign('packing_spec_id')->references('id')->on('supplier_product_packing_specs')->cascadeOnDelete();
            $table->unsignedInteger('packaging_component_id')->index();
            $table->foreign('packaging_component_id')->references('id')->on('packaging_components');
            $table->string('level', 16);
            $table->string('role', 24);
            $table->decimal('quantity', 12, 3);
            $table->decimal('declared_weight_g_each', 12, 3)->nullable();
            $table->decimal('weight_g_each', 12, 3)->nullable();
            $table->boolean('weight_is_measured')->default(false);
            $table->string('fate_at_our_warehouse', 24);
            $table->boolean('is_epr_reportable')->default(true);
            $table->string('description')->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestampsTz();
        });

        Schema::create('stock_delivery_packing_checks', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('group_id');
            $table->foreign('group_id')->references('id')->on('groups');
            $table->unsignedSmallInteger('organisation_id')->index();
            $table->foreign('organisation_id')->references('id')->on('organisations');
            $table->unsignedInteger('stock_delivery_id')->index();
            $table->foreign('stock_delivery_id')->references('id')->on('stock_deliveries')->cascadeOnDelete();
            $table->unsignedBigInteger('stock_delivery_item_id')->index();
            $table->foreign('stock_delivery_item_id')->references('id')->on('stock_delivery_items')->cascadeOnDelete();
            $table->unsignedInteger('packing_spec_id')->index();
            $table->foreign('packing_spec_id')->references('id')->on('supplier_product_packing_specs')->cascadeOnDelete();
            $table->boolean('weigh_materials')->default(false);
            $table->unsignedSmallInteger('cartons_to_check')->default(0);
            $table->jsonb('carton_weights_g')->default('[]');
            $table->decimal('deviation_pct', 8, 2)->nullable();
            $table->string('result', 16)->default('pending')->index();
            $table->unsignedInteger('staff_task_id')->nullable();
            $table->unsignedSmallInteger('checked_by_user_id')->nullable();
            $table->foreign('checked_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->timestampTz('checked_at')->nullable();
            $table->timestampsTz();
            $table->unique(['stock_delivery_item_id', 'packing_spec_id']);
        });

        Schema::create('epr_packing_lines', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedSmallInteger('group_id');
            $table->foreign('group_id')->references('id')->on('groups');
            $table->unsignedSmallInteger('organisation_id');
            $table->foreign('organisation_id')->references('id')->on('organisations');
            $table->date('date');
            $table->string('activity', 24);
            $table->unsignedInteger('stock_delivery_id')->nullable();
            $table->unsignedBigInteger('stock_delivery_item_id')->nullable();
            $table->unsignedSmallInteger('agent_id')->nullable();
            $table->unsignedInteger('supplier_product_id')->nullable();
            $table->unsignedInteger('packing_spec_id')->nullable();
            $table->unsignedInteger('packing_spec_line_id')->nullable();
            $table->unsignedInteger('packaging_component_id')->nullable();
            $table->unsignedSmallInteger('counterparty_country_id')->nullable();
            $table->foreign('counterparty_country_id')->references('id')->on('countries');
            $table->string('level', 16)->nullable();
            $table->string('material_category', 32);
            $table->string('polymer', 16)->nullable();
            $table->string('fate', 24)->nullable();
            $table->string('spec_status', 16)->nullable();
            $table->boolean('weight_is_measured')->default(false);
            $table->boolean('is_household')->default(false);
            $table->string('shop_type', 16)->nullable();
            $table->decimal('cartons', 16, 4);
            $table->decimal('quantity', 16, 4);
            $table->decimal('kg', 16, 4);
            $table->timestampTz('created_at')->nullable();
            $table->index(['organisation_id', 'date', 'activity']);
            $table->index(['organisation_id', 'supplier_product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('epr_packing_lines');
        Schema::dropIfExists('stock_delivery_packing_checks');
        Schema::dropIfExists('packing_spec_lines');
        Schema::dropIfExists('supplier_product_packing_specs');
        Schema::table('packaging_components', function (Blueprint $table) {
            $table->dropUnique(['group_id', 'dropdown_key']);
            $table->dropColumn(['is_dropdown_item', 'dropdown_key', 'dropdown_labels', 'dropdown_position', 'default_role', 'is_epr_reportable', 'needs_review']);
        });
    }
};
