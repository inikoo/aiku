<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->boolean('is_generic')->default(false)->index();
            $table->string('wechat')->nullable();
            $table->string('whatsapp')->nullable();
            $table->unsignedSmallInteger('default_lead_time_days')->nullable();
            $table->jsonb('factory_holidays')->default('[]');
        });

        Schema::table('supplier_products', function (Blueprint $table) {
            $table->jsonb('carton_dimensions')->nullable()->comment('l, w, h in cm');
            $table->timestampTz('lead_time_confirmed_at')->nullable();
            $table->unsignedSmallInteger('lead_time_confirmed_by_user_id')->nullable();
            $table->foreign('lead_time_confirmed_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->string('availability_status')->default('available')->index();
            $table->string('unavailable_reason')->nullable();
            $table->date('available_from')->nullable();
            $table->decimal('availability_new_cost', 18, 4)->nullable();
            $table->text('availability_note')->nullable();
            $table->jsonb('packaging_terms')->default('{}');
        });

        DB::statement("UPDATE supplier_products SET availability_status = 'unavailable' WHERE is_available = false");

        Schema::create('supplier_stock_families', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('group_id')->index();
            $table->foreign('group_id')->references('id')->on('groups');
            $table->unsignedInteger('supplier_id');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->cascadeOnDelete();
            $table->unsignedInteger('stock_family_id')->index();
            $table->foreign('stock_family_id')->references('id')->on('stock_families')->cascadeOnDelete();
            $table->unsignedInteger('minimum_carton_order')->nullable();
            $table->unsignedSmallInteger('estimated_lead_time_days')->nullable();
            $table->jsonb('packaging_terms')->default('{}');
            $table->jsonb('production_calendar')->default('{}');
            $table->timestampsTz();
            $table->unique(['supplier_id', 'stock_family_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_stock_families');

        Schema::table('supplier_products', function (Blueprint $table) {
            $table->dropForeign(['lead_time_confirmed_by_user_id']);
            $table->dropColumn(['carton_dimensions', 'lead_time_confirmed_at', 'lead_time_confirmed_by_user_id', 'availability_status', 'unavailable_reason', 'available_from', 'availability_new_cost', 'availability_note', 'packaging_terms']);
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['is_generic', 'wechat', 'whatsapp', 'default_lead_time_days', 'factory_holidays']);
        });
    }
};
