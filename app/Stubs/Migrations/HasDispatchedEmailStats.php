<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 24 Mar 2023 04:45:48 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use App\Enums\Mail\DispatchedEmail\DispatchedEmailStateEnum;
use Illuminate\Database\Schema\Blueprint;

trait HasDispatchedEmailStats
{
    public function dispatchedEmailStats(Blueprint $table): Blueprint
    {
        $table->unsignedBigInteger('number_dispatched_emails')->default(0);

        foreach (DispatchedEmailStateEnum::cases() as $state) {
            $table->unsignedBigInteger("number_dispatched_emails_state_{$state->snake()}")->default(0);
        }
        $table->unsignedBigInteger('number_provoked_unsubscribe')->default(0);


        return $table;
    }
}
