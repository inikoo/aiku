<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 05 Mar 2023 02:38:20 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('user_stats', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users');

            $table->unsignedSmallInteger('number_logins')->default(0);
            $table->datetime('login_at')->nullable();
            $table->datetime('last_active')->nullable();
            $table->datetime('last_login')->nullable();
            $table->datetime('failed_login')->nullable();
            $table->datetime('failed_login_at')->nullable();
            $table->datetime('active')->nullable();

            $table->unsignedSmallInteger('number_other_tenants')->default(0);
            $table->unsignedSmallInteger('number_other_active_tenants')->default(0);
            $table->timestampsTz();
        });
    }


    public function down()
    {
        Schema::dropIfExists('user_stats');
    }
};
