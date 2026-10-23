<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Created: Fri, 23 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        foreach (['customers', 'orders'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('google_ads_click_id')->nullable();
                $table->string('google_ads_click_id_type', 8)->nullable();
                $table->timestampTz('google_ads_clicked_at')->nullable();
                $table->boolean('google_ads_ad_user_data_consent')->nullable();
            });
        }

        Schema::table('traffic_source_clicks', function (Blueprint $table) {
            $table->string('click_id_type', 8)->nullable();
        });
    }

    public function down(): void
    {
        foreach (['customers', 'orders'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn(['google_ads_click_id', 'google_ads_click_id_type', 'google_ads_clicked_at', 'google_ads_ad_user_data_consent']);
            });
        }

        Schema::table('traffic_source_clicks', function (Blueprint $table) {
            $table->dropColumn('click_id_type');
        });
    }
};
