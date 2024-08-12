<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 12 Aug 2024 12:00:35 Central Indonesia Time, Bali Office, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use App\Enums\Accounting\TopUp\TopUpStatusEnum;
use Illuminate\Database\Schema\Blueprint;

trait HasCreditsStats
{
    public function getCreditTransactionsStats(Blueprint $table): Blueprint
    {

        $table->unsignedInteger('number_credit_transactions')->default(0);


        return $table;
    }

    public function getTopUpsStats(Blueprint $table): Blueprint
    {

        $table->unsignedInteger('number_top_ups')->default(0);
        foreach (TopUpStatusEnum::cases() as $state) {
            $table->unsignedInteger("number_top_ups_status_{$state->snake()}")->default(0);
        }

        return $table;
    }
}
