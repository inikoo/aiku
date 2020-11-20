<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Tue, 17 Nov 2020 13:49:16 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class WebpagesEmails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('webpages', function (Blueprint $table) {
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
        });

        Schema::create('web_blocks', function (Blueprint $table) {
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
        });
        Schema::create('email_services', function (Blueprint $table) {
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
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('email_services');
        Schema::dropIfExists('web_blocks');
        Schema::dropIfExists('webpages');
    }
}
