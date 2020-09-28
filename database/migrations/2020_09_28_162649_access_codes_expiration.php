<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Tue, 29 Sep 2020 00:26:57 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AccessCodesExpiration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('access_codes', function (Blueprint $table) {

            $table->timestampTz('expired_at')->nullable()->index();
            $table->dropColumn('ttl');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('access_codes', function (Blueprint $table) {
            $table->dropColumn('expired_at');
            $table->unsignedMediumInteger('ttl')->default(3600*24)->comment('Seconds before code expires');
        });
    }
}
