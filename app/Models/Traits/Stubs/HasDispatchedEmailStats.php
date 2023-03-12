<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 12 Mar 2023 03:04:56 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Models\Traits\Stubs;

use App\Enums\Mailroom\DispatchedEmail\DispatchedEmailStateEnum;
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
