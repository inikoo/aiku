<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 06 Jun 2023 19:53:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use App\Enums\CRM\Customer\CustomerStateEnum;
use App\Enums\CRM\Customer\CustomerTradeStateEnum;
use App\Enums\CRM\Poll\PollTypeEnum;
use App\Enums\CRM\WebUser\WebUserAuthTypeEnum;
use App\Enums\CRM\WebUser\WebUserTypeEnum;
use Illuminate\Database\Schema\Blueprint;

trait HasCRMStats
{
    public function customerStats(Blueprint $table): Blueprint
    {
        $table->unsignedInteger('number_customers')->default(0);

        foreach (CustomerStateEnum::cases() as $customerState) {
            $table->unsignedInteger("number_customers_state_{$customerState->snake()}")->default(0);
        }
        foreach (CustomerTradeStateEnum::cases() as $tradeState) {
            $table->unsignedInteger('number_customers_trade_state_'.$tradeState->snake())->default(0);
        }


        return $table;
    }

    public function getWebUsersStatsFields(Blueprint $table): Blueprint
    {
        $table->unsignedInteger('number_web_users')->default(0);
        $table->unsignedInteger('number_current_web_users')->default(0)->comment('Number of web users with state = true');

        foreach (WebUserTypeEnum::cases() as $case) {
            $table->unsignedInteger('number_web_users_type_'.$case->snake())->default(0);
        }

        foreach (WebUserAuthTypeEnum::cases() as $case) {
            $table->unsignedInteger('number_web_users_auth_type_'.$case->snake())->default(0);
        }

        return $table;
    }

    public function getPollsStatsFields(Blueprint $table): Blueprint
    {
        $table->unsignedSmallInteger('number_polls')->default(0);
        $table->unsignedSmallInteger('number_polls_in_registration')->default(0);
        $table->unsignedSmallInteger('number_polls_required_in_registration')->default(0);
        $table->unsignedSmallInteger('number_polls_in_iris')->default(0);
        $table->unsignedSmallInteger('number_polls_required_in_iris')->default(0);


        foreach (PollTypeEnum::cases() as $case) {
            $table->unsignedInteger('number_polls_type_'.$case->snake())->default(0);
            $table->unsignedSmallInteger('number_polls_in_registration_type_'.$case->snake())->default(0);
            $table->unsignedSmallInteger('number_polls_required_in_registration_type_'.$case->snake())->default(0);
            $table->unsignedSmallInteger('number_polls_in_iris_type_'.$case->snake())->default(0);
            $table->unsignedSmallInteger('number_polls_required_in_iris_type_'.$case->snake())->default(0);
        }

        return $table;
    }


}
