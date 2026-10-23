<?php

/*
 * Author: Andi Ferdiawan <andiferdiawan22@gmail.com>
 * Created: Thu, 22 Oct 2026
 * Copyright (c) 2026, Andi Ferdiawan
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('social_media_posts', function (Blueprint $table) {
            $table->boolean('is_boosted')->default(false)->index();
            $table->unsignedBigInteger('ad_views')->nullable();
            $table->unsignedBigInteger('ad_reach')->nullable();
            $table->unsignedBigInteger('ad_link_clicks')->nullable();
        });

        Schema::table('social_media_account_daily_stats', function (Blueprint $table) {
            $table->unsignedBigInteger('ad_impressions')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('social_media_posts', function (Blueprint $table) {
            $table->dropColumn(['is_boosted', 'ad_views', 'ad_reach', 'ad_link_clicks']);
        });

        Schema::table('social_media_account_daily_stats', function (Blueprint $table) {
            $table->dropColumn('ad_impressions');
        });
    }
};
