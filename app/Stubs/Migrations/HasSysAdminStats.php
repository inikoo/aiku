<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 06 Jul 2023 12:33:47 Malaysia Time, plane Bali -> KL
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use App\Enums\SysAdmin\User\UserTypeEnum;
use Illuminate\Database\Schema\Blueprint;

trait HasSysAdminStats
{
    public function guestsStatsFields(Blueprint $table): Blueprint
    {
        $table->unsignedSmallInteger('number_guests')->default(0);
        $table->unsignedSmallInteger('number_guests_status_active')->default(0);
        $table->unsignedSmallInteger('number_guests_status_inactive')->default(0);


        return $table;
    }

    public function userStatsFields(Blueprint $table): Blueprint
    {
        $table->unsignedSmallInteger('number_users')->default(0);
        $table->unsignedSmallInteger('number_users_status_active')->default(0);
        $table->unsignedSmallInteger('number_users_status_inactive')->default(0);

        foreach (UserTypeEnum::cases() as $userType) {
            $table->unsignedSmallInteger('number_users_type_'.$userType->snake())->default(0);
        }

        return $table;
    }
}
