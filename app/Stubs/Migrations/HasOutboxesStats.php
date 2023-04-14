<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 14 Apr 2023 10:21:27 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use App\Enums\Mail\Outbox\OutboxStateEnum;
use App\Enums\Mail\Outbox\OutboxTypeEnum;
use Illuminate\Database\Schema\Blueprint;

trait HasOutboxesStats
{
    public function outboxesStats(Blueprint $table): Blueprint
    {
        $table->unsignedSmallInteger('number_outboxes')->default(0);
        foreach (OutboxTypeEnum::cases() as $outboxTypeEnum) {
            $table->unsignedInteger('number_outbox_type_'.$outboxTypeEnum->snake())->default(0);
        }
        foreach (OutboxStateEnum::cases() as $outboxStateEnum) {
            $table->unsignedInteger('number_outbox_state_'.$outboxStateEnum->snake())->default(0);
        }


        return $table;
    }
}
