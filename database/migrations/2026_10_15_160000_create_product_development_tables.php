<?php

use App\Enums\Goods\ProductDevelopment\ProductDevelopmentChecklistItemStateEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentDesignRoundStatusEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentMemberRoleEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentStageStateEnum;
use App\Enums\Goods\ProductDevelopment\ProductDevelopmentStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('product_developments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('group_id')->index();
            $table->foreign('group_id')->references('id')->on('groups');
            $table->string('slug')->unique()->collation('und_ns');
            $table->unsignedInteger('number');
            $table->string('code')->unique();
            $table->string('name');
            $table->string('type')->index();
            $table->string('status')->default(ProductDevelopmentStatusEnum::ACTIVE->value)->index();
            $table->unsignedSmallInteger('current_position')->default(1)->index();
            $table->string('checklist_category')->nullable()->index();
            $table->unsignedInteger('owner_id')->nullable()->index();
            $table->foreign('owner_id')->references('id')->on('users');
            $table->unsignedInteger('trade_unit_family_id')->nullable()->index();
            $table->foreign('trade_unit_family_id')->references('id')->on('trade_unit_families')->nullOnDelete();
            $table->unsignedInteger('packaging_family_id')->nullable()->index();
            $table->foreign('packaging_family_id')->references('id')->on('packaging_families')->nullOnDelete();
            $table->unsignedInteger('supplier_id')->nullable()->index();
            $table->foreign('supplier_id')->references('id')->on('suppliers')->nullOnDelete();
            $table->unsignedSmallInteger('agent_id')->nullable()->index();
            $table->foreign('agent_id')->references('id')->on('agents')->nullOnDelete();
            $table->date('target_landing_date')->nullable();
            $table->date('target_online_date')->nullable();
            $table->text('brief')->nullable();
            $table->jsonb('markets');
            $table->jsonb('data');
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        DB::statement('CREATE SEQUENCE product_development_number_seq');

        Schema::create('product_development_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('product_development_id')->unique();
            $table->foreign('product_development_id')->references('id')->on('product_developments')->cascadeOnDelete();
            $table->unsignedSmallInteger('number_items')->default(0);
            $table->unsignedSmallInteger('number_checklist_items')->default(0);
            $table->unsignedSmallInteger('number_required_missing')->default(0);
            foreach (ProductDevelopmentMemberRoleEnum::cases() as $role) {
                $table->unsignedSmallInteger($role->statsColumn())->default(0);
            }
            $table->unsignedSmallInteger('number_open_comments')->default(0);
            $table->timestampsTz();
        });

        Schema::create('product_development_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('product_development_id')->index();
            $table->foreign('product_development_id')->references('id')->on('product_developments')->cascadeOnDelete();
            $table->unsignedInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('role');
            $table->timestampsTz();
            $table->unique(['product_development_id', 'user_id', 'role']);
        });

        Schema::create('product_development_stages', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('product_development_id')->index();
            $table->foreign('product_development_id')->references('id')->on('product_developments')->cascadeOnDelete();
            $table->unsignedSmallInteger('position');
            $table->string('sub_position', 1)->nullable();
            $table->string('stage');
            $table->string('role');
            $table->string('state')->default(ProductDevelopmentStageStateEnum::PENDING->value);
            $table->date('due_at')->nullable();
            $table->timestampTz('started_at')->nullable();
            $table->timestampTz('completed_at')->nullable();
            $table->unsignedInteger('completed_by_id')->nullable();
            $table->foreign('completed_by_id')->references('id')->on('users');
            $table->timestampsTz();
            $table->unique(['product_development_id', 'stage']);
        });

        Schema::create('product_development_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('product_development_id')->index();
            $table->foreign('product_development_id')->references('id')->on('product_developments')->cascadeOnDelete();
            $table->unsignedInteger('trade_unit_id')->index();
            $table->foreign('trade_unit_id')->references('id')->on('trade_units');
            $table->unsignedInteger('supplier_product_id')->nullable()->index();
            $table->foreign('supplier_product_id')->references('id')->on('supplier_products')->nullOnDelete();
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestampsTz();
            $table->unique(['product_development_id', 'trade_unit_id']);
        });

        Schema::create('product_development_checklist_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('group_id')->index();
            $table->foreign('group_id')->references('id')->on('groups');
            $table->string('category')->index();
            $table->string('role');
            $table->string('level');
            $table->string('key');
            $table->string('label');
            $table->boolean('required')->default(true);
            $table->string('target_field')->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
            $table->unique(['group_id', 'category', 'key']);
        });

        Schema::create('product_development_checklist_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('product_development_id')->index();
            $table->foreign('product_development_id')->references('id')->on('product_developments')->cascadeOnDelete();
            $table->unsignedInteger('product_development_item_id')->nullable()->index();
            $table->foreign('product_development_item_id')->references('id')->on('product_development_items')->cascadeOnDelete();
            $table->unsignedInteger('template_id')->nullable()->index();
            $table->foreign('template_id')->references('id')->on('product_development_checklist_templates')->nullOnDelete();
            $table->string('role')->index();
            $table->string('level');
            $table->string('key');
            $table->string('label');
            $table->boolean('required')->default(true);
            $table->string('target_field')->nullable();
            $table->string('state')->default(ProductDevelopmentChecklistItemStateEnum::MISSING->value)->index();
            $table->jsonb('value')->nullable();
            $table->text('na_reason')->nullable();
            $table->unsignedInteger('completed_by_id')->nullable();
            $table->foreign('completed_by_id')->references('id')->on('users');
            $table->timestampTz('completed_at')->nullable();
            $table->timestampsTz();
            $table->unique(['product_development_id', 'product_development_item_id', 'key'])->nullsNotDistinct();
        });

        Schema::create('product_development_design_rounds', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('product_development_id')->index();
            $table->foreign('product_development_id')->references('id')->on('product_developments')->cascadeOnDelete();
            $table->unsignedInteger('product_development_item_id')->nullable()->index();
            $table->foreign('product_development_item_id')->references('id')->on('product_development_items')->cascadeOnDelete();
            $table->string('round');
            $table->unsignedSmallInteger('version')->default(1);
            $table->string('status')->default(ProductDevelopmentDesignRoundStatusEnum::DRAFT->value);
            $table->unsignedInteger('approved_by_id')->nullable();
            $table->foreign('approved_by_id')->references('id')->on('users');
            $table->timestampTz('approved_at')->nullable();
            $table->timestampTz('locked_at')->nullable();
            $table->timestampsTz();
        });

        Schema::create('product_development_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('product_development_id')->index();
            $table->foreign('product_development_id')->references('id')->on('product_developments')->cascadeOnDelete();
            $table->unsignedInteger('product_development_item_id')->nullable()->index();
            $table->foreign('product_development_item_id')->references('id')->on('product_development_items')->nullOnDelete();
            $table->unsignedInteger('checklist_item_id')->nullable()->index();
            $table->foreign('checklist_item_id')->references('id')->on('product_development_checklist_items')->nullOnDelete();
            $table->unsignedInteger('design_round_id')->nullable()->index();
            $table->foreign('design_round_id')->references('id')->on('product_development_design_rounds')->nullOnDelete();
            $table->unsignedInteger('media_id')->nullable()->index();
            $table->foreign('media_id')->references('id')->on('media')->nullOnDelete();
            $table->string('scope')->index();
            $table->unsignedSmallInteger('version')->default(1);
            $table->timestampTz('superseded_at')->nullable();
            $table->unsignedInteger('uploaded_by_id')->nullable();
            $table->foreign('uploaded_by_id')->references('id')->on('users');
            $table->timestampsTz();
        });

        Schema::create('product_development_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('product_development_id')->index();
            $table->foreign('product_development_id')->references('id')->on('product_developments')->cascadeOnDelete();
            $table->nullableMorphs('model');
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->foreign('parent_id')->references('id')->on('product_development_comments')->nullOnDelete();
            $table->unsignedInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users');
            $table->text('body');
            $table->jsonb('mentions');
            $table->jsonb('data');
            $table->string('status')->nullable();
            $table->unsignedInteger('resolved_by_id')->nullable();
            $table->foreign('resolved_by_id')->references('id')->on('users');
            $table->timestampTz('resolved_at')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        Schema::create('product_development_purchase_order', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('product_development_id')->index();
            $table->foreign('product_development_id')->references('id')->on('product_developments')->cascadeOnDelete();
            $table->unsignedInteger('purchase_order_id')->index();
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->cascadeOnDelete();
            $table->timestampsTz();
            $table->unique(['product_development_id', 'purchase_order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_development_purchase_order');
        Schema::dropIfExists('product_development_comments');
        Schema::dropIfExists('product_development_files');
        Schema::dropIfExists('product_development_design_rounds');
        Schema::dropIfExists('product_development_checklist_items');
        Schema::dropIfExists('product_development_checklist_templates');
        Schema::dropIfExists('product_development_items');
        Schema::dropIfExists('product_development_stages');
        Schema::dropIfExists('product_development_members');
        Schema::dropIfExists('product_development_stats');
        DB::statement('DROP SEQUENCE IF EXISTS product_development_number_seq');
        Schema::dropIfExists('product_developments');
    }
};
