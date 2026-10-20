<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('seo_keyword_tags', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('shop_id');
            $table->foreign('shop_id')->references('id')->on('shops')->cascadeOnDelete();
            $table->string('name', 64);
            $table->timestampsTz();
            $table->unique(['shop_id', 'name']);
        });

        Schema::create('seo_keyword_tag_keyword', function (Blueprint $table) {
            $table->unsignedInteger('tag_id');
            $table->foreign('tag_id')->references('id')->on('seo_keyword_tags')->cascadeOnDelete();
            $table->string('keyword', 255);
            $table->timestampTz('created_at')->nullable();
            $table->primary(['tag_id', 'keyword']);
            $table->index('keyword');
        });

        Schema::table('seo_tracked_keywords', function (Blueprint $table) {
            $table->jsonb('serp_features_owned')->nullable();
        });

        Schema::table('seo_keyword_rankings', function (Blueprint $table) {
            $table->jsonb('serp_features_owned')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('seo_keyword_rankings', function (Blueprint $table) {
            $table->dropColumn('serp_features_owned');
        });

        Schema::table('seo_tracked_keywords', function (Blueprint $table) {
            $table->dropColumn('serp_features_owned');
        });

        Schema::dropIfExists('seo_keyword_tag_keyword');
        Schema::dropIfExists('seo_keyword_tags');
    }
};
