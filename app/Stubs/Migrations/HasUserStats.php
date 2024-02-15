<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Feb 2024 17:42:40 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use Illuminate\Database\Schema\Blueprint;

trait HasUserStats
{
    public function userStats(Blueprint $table): Blueprint
    {
        $table->unsignedInteger('number_logins')->default(0);
        $table->datetime('last_login_at')->nullable();
        $table->string('last_login_ip')->nullable();
        $table->datetime('last_active_at')->nullable();
        $table->unsignedInteger('number_failed_logins')->default(0);
        $table->string('last_failed_login_ip')->nullable();
        $table->datetime('last_failed_login_at')->nullable();

        return $table;
    }
}
