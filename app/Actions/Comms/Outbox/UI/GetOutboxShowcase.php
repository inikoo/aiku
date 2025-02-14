<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:35 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\Outbox\UI;

use App\Enums\Comms\DispatchedEmail\DispatchedEmailStateEnum;
use App\Models\Comms\Outbox;
use Lorisleiva\Actions\Concerns\AsObject;

class GetOutboxShowcase
{
    use AsObject;

    public function handle(Outbox $outbox): array
    {
        $stats = $outbox->stats;
        $stats = [
            [
                'label' => DispatchedEmailStateEnum::labels()['sent'],
                'key'   => 'dispatched_emails_state_sent',
                'icon'  => DispatchedEmailStateEnum::stateIcon()['sent']['icon'],
                'class' => 'border-blue-50 bg-blue-300' ,
                'color' => '#fb923c',
                'value' => $stats->number_dispatched_emails_state_sent,
            ],
            [
                'label' => DispatchedEmailStateEnum::labels()['delivered'],
                'key'   => 'dispatched_emails_state_delivered',
                'icon'  => DispatchedEmailStateEnum::stateIcon()['delivered']['icon'],
                'class' => 'border-blue-50 bg-blue-300' ,
                'color' => '#374151',
                'value' => $stats->number_dispatched_emails_state_delivered,
            ],
            [
                'label' => DispatchedEmailStateEnum::labels()['delivered'],
                'key'   => 'dispatched_emails_state_soft_bounce',
                'icon'  => DispatchedEmailStateEnum::stateIcon()['delivered']['icon'],
                'class' => 'border-blue-50 bg-blue-300' ,
                'color' => '#a3e635',
                'value' => $stats->number_dispatched_emails_state_soft_bounce,
            ],
            [
                'label' => DispatchedEmailStateEnum::labels()['opened'],
                'key'   => 'dispatched_emails_state_opened',
                'icon'  => DispatchedEmailStateEnum::stateIcon()['opened']['icon'],
                'class' => 'border-green-50 bg-green-300' ,
                'color' => '#9ca3af',
                'value' => $stats->number_dispatched_emails_state_opened,
            ],
            [
                'label' => DispatchedEmailStateEnum::labels()['clicked'],
                'key'   => 'dispatched_emails_state_clicked',
                'icon'  => DispatchedEmailStateEnum::stateIcon()['clicked']['icon'],
                'class' => 'border-green-50 bg-green-300' ,
                'color' => '#4ade80',
                'value' => $stats->number_dispatched_emails_state_clicked,
            ],
            [
                'label' => DispatchedEmailStateEnum::labels()['spam'],
                'key'   => 'dispatched_emails_state_spam',
                'icon'  => DispatchedEmailStateEnum::stateIcon()['spam']['icon'],
                'class' => 'border-orange-50 bg-orange-300' ,
                'color' => '#60a5fa',
                'value' => $stats->number_dispatched_emails_state_spam,
            ],
            [
                'label' => DispatchedEmailStateEnum::labels()['soft_bounce'],
                'key'   => 'dispatched_emails_state_delivered',
                'icon'  => DispatchedEmailStateEnum::stateIcon()['soft_bounce']['icon'],
                'class' => 'border-orange-50 bg-orange-300' ,
                'color' => '#f87171',
                'value' => $stats->number_dispatched_emails_state_delivereds,
            ],
            [
                'label' => DispatchedEmailStateEnum::labels()['unsubscribed'],
                'key'   => 'dispatched_emails_state_unsubscribed',
                'icon'  => DispatchedEmailStateEnum::stateIcon()['unsubscribed']['icon'],
                'class' => 'border-red-50 bg-red-300' ,
                'color' => '#f87171',
                'value' => $stats->number_dispatched_emails_state_unsubscribed,
            ],
            [
                'label' => DispatchedEmailStateEnum::labels()['ready'],
                'key'   => 'dispatched_emails_state_ready',
                'icon'  => DispatchedEmailStateEnum::stateIcon()['ready']['icon'],
                'class' => 'border-red-50 bg-red-300' ,
                'color' => '#f87171',
                'value' => $stats->number_dispatched_emails_state_ready,
            ],
            [
                'label' => DispatchedEmailStateEnum::labels()['sent_to_provider'],
                'key'   => 'dispatched_emails_state_sent_to_provider',
                'icon'  => DispatchedEmailStateEnum::stateIcon()['sent_to_provider']['icon'],
                'class' => 'border-red-50 bg-red-300' ,
                'color' => '#f87171',
                'value' => $stats->number_dispatched_emails_state_sent_to_provider,
            ],
            [
                'label' => DispatchedEmailStateEnum::labels()['rejected_by_provider'],
                'key'   => 'dispatched_emails_state_rejected_by_provider',
                'icon'  => DispatchedEmailStateEnum::stateIcon()['rejected_by_provider']['icon'],
                'class' => 'border-red-50 bg-red-300' ,
                'color' => '#f87171',
                'value' => $stats->number_dispatched_emails_state_rejected_by_provider,
            ],
            [
                'label' => DispatchedEmailStateEnum::labels()['hard_bounce'],
                'key'   => 'dispatched_emails_state_delivered',
                'icon'  => DispatchedEmailStateEnum::stateIcon()['hard_bounce']['icon'],
                'class' => 'border-red-50 bg-red-300' ,
                'color' => '#f87171',
                'value' => $stats->number_dispatched_emails_state_hard_bounce,
            ],
        ];

        return [
                'outbox' => [
                    'slug'  => $outbox->slug
                ],
                'state' => $outbox->state,
                'builder' => $outbox->builder,
                'compiled_layout' => $outbox->emailOngoingRun?->email?->liveSnapshot?->compiled_layout,
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
