<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:35 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\Outbox\UI;

use App\Enums\Comms\DispatchedEmail\DispatchedEmailStateEnum;
use App\Models\Comms\Outbox;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsObject;

class GetOutboxShowcase
{
    use AsObject;

    public function handle(Outbox $outbox): array
    {
        $stats = [];
        $outboxStats = $outbox->stats;
        foreach (DispatchedEmailStateEnum::cases() as $state) {
            $stats[] = [
                'label' => $state::labels()[$state->value],
                'key'   => 'dispatched_emails_state_' . $state->value,
                'icon'  => $state::stateIcon()[$state->value]['icon'],
                'value' => $outboxStats->{'number_dispatched_emails_state_' . $state->value},
            ];
        }

        return [
                'outbox' => [
                    'slug'  => $outbox->slug,
                    'subject' => $outbox->emailOngoingRun?->email?->subject,
                    'sender' => $outbox->shop?->senderEmail?->email_address
                ],
                'state' => $outbox->state,
                'builder' => $outbox->builder,
               'compiled_layout' => ($outbox->builder->value == "blade")
                    ? Arr::get($outbox->emailOngoingRun?->email?->liveSnapshot?->layout, 'blade_template')
                    : $outbox->emailOngoingRun?->email?->liveSnapshot?->compiled_layout,

                'dashboard_stats' => [
                    'widgets' => [
                        'column_count' => 2,
                        'components' => [
                            [
                                'type' => 'circle_display',

                                'data' => $stats
                            ]
                        ]
                    ]
                ]
        ];
    }
}
