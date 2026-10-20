<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('product_development_design_rounds', function (Blueprint $table) {
            $table->text('notes')->nullable();
            $table->unsignedInteger('submitted_by_id')->nullable();
            $table->foreign('submitted_by_id')->references('id')->on('users');
            $table->timestampTz('submitted_at')->nullable();
            $table->jsonb('data')->default('{}');
        });
    }

    public function down(): void
    {
        Schema::table('product_development_design_rounds', function (Blueprint $table) {
            $table->dropForeign(['submitted_by_id']);
            $table->dropColumn(['notes', 'submitted_by_id', 'submitted_at', 'data']);
        });
    }
};
