<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 12 Aug 2022 18:34:11 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrganisationsTable extends Migration
{
    public function up()
    {
        Schema::create('organisations', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('code')->unique();
            $table->string('name');
            $table->jsonb('data');
            $table->unsignedSmallInteger('country_id');
            $table->foreign('country_id')->references('id')->on('countries');
            $table->unsignedSmallInteger('language_id');
            $table->foreign('language_id')->references('id')->on('languages');
            $table->unsignedSmallInteger('timezone_id');
            $table->foreign('timezone_id')->references('id')->on('timezones');
            $table->unsignedSmallInteger('currency_id')->comment('Organisation accounting currency');
            $table->foreign('currency_id')->references('id')->on('currencies');
            $table->unsignedSmallInteger('number_users')->default(0);
            $table->timestampsTz();
            $table->softDeletesTz();
        });

        Schema::create('organisation_user', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained();
            $table->foreignId('organisation_id')->nullable()->constrained();

            $table->timestampsTz();
            $table->unique(['user_id', 'organisation_id']);
        });

        Schema::table('users', function($table) {
            $table->foreignId('organisation_id')->constrained()->nullable();
        });

    }


    public function down(): void
    {
        Schema::table('users', function($table) {
            $table->dropColumn('organisation_id');
        });
        Schema::dropIfExists('organisation_user');
        Schema::dropIfExists('organisations');
    }
}
