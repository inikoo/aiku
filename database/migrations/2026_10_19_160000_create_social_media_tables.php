<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

use App\Stubs\Migrations\HasGroupOrganisationRelationship;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    use HasGroupOrganisationRelationship;

    /**
     * Organic social media performance, as the platforms report it. Filled today by uploading the
     * exports of Meta Business Suite and TikTok Studio; an API sync can later write the same rows.
     *
     * A post's figures keep growing for weeks after it is published, so a post re-imported is a post
     * corrected, never a post added again: the last export read wins. The same holds for an account's
     * day.
     */
    public function up(): void
    {
        Schema::create('social_media_accounts', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table = $this->groupOrgRelationship($table);
            $table->unsignedSmallInteger('shop_id')->index();
            $table->foreign('shop_id')->references('id')->on('shops');
            $table->string('platform')->index();
            $table->string('external_id')->nullable();
            $table->string('name');
            $table->string('username')->nullable();
            $table->unsignedInteger('followers')->nullable();
            $table->date('followers_at')->nullable();
            $table->timestampsTz();

            $table->unique(['shop_id', 'platform', 'external_id']);
        });

        Schema::create('social_media_posts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('social_media_account_id');
            $table->foreign('social_media_account_id')->references('id')->on('social_media_accounts')->cascadeOnDelete();
            $table->string('external_id');
            $table->string('type')->index();
            $table->text('description')->nullable();
            $table->string('permalink', 1024)->nullable();
            $table->dateTimeTz('published_at')->index();
            $table->unsignedBigInteger('reach')->default(0);
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('reactions')->default(0);
            $table->unsignedBigInteger('comments')->default(0);
            $table->unsignedBigInteger('shares')->default(0);
            $table->unsignedBigInteger('saves')->default(0);
            $table->unsignedBigInteger('link_clicks')->default(0);
            $table->unsignedBigInteger('follows')->default(0);
            $table->timestampsTz();

            $table->unique(['social_media_account_id', 'external_id']);
        });

        Schema::create('social_media_account_daily_stats', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('social_media_account_id');
            $table->foreign('social_media_account_id')->references('id')->on('social_media_accounts')->cascadeOnDelete();
            $table->date('date')->index();
            $table->unsignedInteger('followers')->nullable();
            $table->unsignedInteger('new_follows')->nullable();
            $table->unsignedBigInteger('reach')->nullable();
            $table->unsignedBigInteger('impressions')->nullable();
            $table->unsignedBigInteger('engagements')->nullable();
            $table->unsignedBigInteger('profile_visits')->nullable();
            $table->unsignedBigInteger('link_clicks')->nullable();
            $table->timestampsTz();

            $table->unique(['social_media_account_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_media_account_daily_stats');
        Schema::dropIfExists('social_media_posts');
        Schema::dropIfExists('social_media_accounts');
    }
};
