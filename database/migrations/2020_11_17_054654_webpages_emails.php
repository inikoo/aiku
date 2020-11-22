<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Tue, 17 Nov 2020 13:49:16 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class WebpagesEmails extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {

        Schema::create(
            'webpages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->index();
            $table->unsignedMediumInteger('website_id');
            $table->foreign('website_id')->references('id')->on('websites');
            $table->nullableMorphs('webpageable');
            $table->string('status')->index();
            $table->string('state')->index();
            $table->jsonb('data');
            $table->jsonb('settings');
            $table->timestampsTz();
            $table->unsignedSmallInteger('tenant_id');
            $table->unsignedMediumInteger('legacy_id')->index()->nullable();
            $table->unique(
                [
                    'legacy_id',
                    'tenant_id'
                ]
            );
        }
        );

        Schema::create(
            'web_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('type')->index();
            $table->unsignedMediumInteger('webpage_id');
            $table->foreign('webpage_id')->references('id')->on('webpages');
            $table->string('slug');
            $table->string('status')->index();
            $table->smallInteger('precedence')->default(0);
            $table->json('settings');
            $table->json('data');
            $table->timestampsTz();
            $table->unsignedSmallInteger('tenant_id');
        }
        );
        Schema::create(
            'email_services', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->index();
            $table->string('type')->index();
            $table->string('subtype')->index();
            $table->morphs('container');

            $table->boolean('status')->index();
            $table->string('state')->index();
            $table->jsonb('data');
            $table->jsonb('settings');
            $table->timestampsTz();

            $table->unsignedSmallInteger('tenant_id');
            $table->unsignedMediumInteger('legacy_id')->index()->nullable();
            $table->unique(
                [
                    'legacy_id',
                    'tenant_id'
                ]
            );
        }
        );

        Schema::create(
            'mailshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_service_id')->constrained();
            $table->string('slug')->index();
            $table->string('name')->index();
            $table->string('state')->index();
            $table->jsonb('data');
            $table->jsonb('settings');
            $table->timestampsTz();

            $table->unsignedSmallInteger('tenant_id');
            $table->unsignedMediumInteger('legacy_id')->index()->nullable();
            $table->unique(
                [
                    'legacy_id',
                    'tenant_id'
                ]
            );
            $table->index('email_service_id');
        }
        );

        Schema::create(
            'email_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_service_id')->constrained();


            $table->string('name')->index();

            //$table->morphs('parent');


            $table->jsonb('data');
            $table->jsonb('settings');
            $table->timestampsTz();
            $table->softDeletesTz();

            $table->unsignedSmallInteger('tenant_id');
            $table->unsignedMediumInteger('legacy_id')->index()->nullable();
            $table->unique(
                [
                    'legacy_id',
                    'tenant_id'
                ]
            );
            $table->index('email_service_id');
        }
        );
        Schema::create(
            'published_email_templates', function (Blueprint $table) {
            $table->id();;
            $table->unsignedMediumInteger('email_template_id')->nullable()->index();
            $table->foreign('email_template_id')->references('id')->on('email_templates');

            $table->foreignId('email_service_id')->constrained();

            $table->jsonb('data');
            $table->timestampsTz();

            $table->unsignedSmallInteger('tenant_id');
            $table->unsignedMediumInteger('legacy_id')->index()->nullable();
            $table->unique(
                [
                    'legacy_id',
                    'tenant_id'
                ]
            );
        }
        );

        Schema::create(
            'emails', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->timestampsTz();
        }
        );

        Schema::create(
            'email_trackings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('email_id')->constrained();
            $table->morphs('parent');
            $table->morphs('recipient');

            $table->string('state')->index();
            $table->string('sender_id')->nullable()->index();
            $table->foreignId('published_email_template_id')->nullable()->constrained();

            $table->jsonb('data');

            $table->timestampsTz();

            $table->unsignedSmallInteger('tenant_id');
            $table->unsignedMediumInteger('legacy_id')->index()->nullable();
            $table->unique(
                [
                    'legacy_id',
                    'tenant_id'
                ]
            );
        }
        );

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('email_trackings');
        Schema::dropIfExists('emails');

        Schema::dropIfExists('published_email_templates');
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('mailshots');
        Schema::dropIfExists('email_services');
        Schema::dropIfExists('web_blocks');
        Schema::dropIfExists('webpages');
    }
}
