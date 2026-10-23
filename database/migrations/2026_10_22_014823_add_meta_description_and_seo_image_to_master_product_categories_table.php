<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('master_product_categories', function (Blueprint $table) {
            $table->text('meta_description')->nullable();
            $table->unsignedInteger('seo_image_id')->nullable();
            $table->foreign('seo_image_id')->references('id')->on('media')->nullOnDelete();
            $table->text('seo_image_url')->nullable();
            $table->string('seo_image_alt')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('master_product_categories', function (Blueprint $table) {
            $table->dropForeign(['seo_image_id']);
            $table->dropColumn(['meta_description', 'seo_image_id', 'seo_image_url', 'seo_image_alt']);
        });
    }
};
