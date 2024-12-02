<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 22 Nov 2024 13:14:28 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Stubs\Migrations;

use App\Enums\Comms\Outbox\OutboxTypeEnum;

trait HasCommsIntervals
{
    use HasDateIntervalsStats;

    protected function getCommsIntervals($table)
    {
        foreach (OutboxTypeEnum::cases() as $case) {
            $fields = [
                $case->value.'_dispatched_emails',
                $case->value.'_opened_emails',
                $case->value.'_clicked_emails',
                $case->value.'_unsubscribed_emails',
                $case->value.'_bounced_emails'
            ];

            if (in_array($case, [
                OutboxTypeEnum::CUSTOMER_NOTIFICATION,
                OutboxTypeEnum::USER_NOTIFICATION
            ])) {
                unset($fields[$case->value.'_unsubscribed_emails']);
            }

            if ($case == OutboxTypeEnum::USER_NOTIFICATION) {
                unset($fields[$case->value.'_opened_emails']);
                unset($fields[$case->value.'_clicked_emails']);
            }

            $table = $this->unsignedIntegerDateIntervals($table, $fields);
        }

        return $table;
    }
}
