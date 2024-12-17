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
                'class' => 'bg-blue-200' ,
                'color' => 'orange',
                'value' => $stats->number_dispatched_emails_state_sent,
            ],
            [
                'label' => DispatchedEmailStateEnum::labels()['delivered'],
                'key'   => 'dispatched_emails_state_delivered',
                'icon'  => DispatchedEmailStateEnum::stateIcon()['delivered']['icon'],
                'class' => 'bg-blue-200' ,
                'color' => 'black',
                'value' => $stats->number_dispatched_emails_state_delivered,
            ],
            [
                'label' => DispatchedEmailStateEnum::labels()['delivered'],
                'key'   => 'dispatched_emails_state_soft_bounce',
                'icon'  => DispatchedEmailStateEnum::stateIcon()['delivered']['icon'],
                'class' => 'bg-blue-200' ,
                'color' => 'lime',
                'value' => $stats->number_dispatched_emails_state_soft_bounce,
            ],
            [
                'label' => DispatchedEmailStateEnum::labels()['opened'],
                'key'   => 'dispatched_emails_state_opened',
                'icon'  => DispatchedEmailStateEnum::stateIcon()['opened']['icon'],
                'class' => 'bg-green-100' ,
                  'color' => 'gray',
                'value' => $stats->number_dispatched_emails_state_opened,
            ],
            [
                'label' => DispatchedEmailStateEnum::labels()['clicked'],
                'key'   => 'dispatched_emails_state_clicked',
                'icon'  => DispatchedEmailStateEnum::stateIcon()['clicked']['icon'],
                'class' => 'bg-green-100' ,
                  'color' => 'green',
                'value' => $stats->number_dispatched_emails_state_clicked,
            ],
            [
                'label' => DispatchedEmailStateEnum::labels()['spam'],
                'key'   => 'dispatched_emails_state_spam',
                'icon'  => DispatchedEmailStateEnum::stateIcon()['spam']['icon'],
                'class' => 'bg-orange-200' ,
                  'color' => 'blue',
                'value' => $stats->number_dispatched_emails_state_spam,
            ],
            [
                'label' => DispatchedEmailStateEnum::labels()['soft-bounce'],
                'key'   => 'dispatched_emails_state_delivered',
                'icon'  => DispatchedEmailStateEnum::stateIcon()['soft-bounce']['icon'],
                'class' => 'bg-orange-200' ,
                'color' => 'rgb(59 130 56)',
                'value' => $stats->number_dispatched_emails_state_delivereds,
            ],
            [
                'label' => DispatchedEmailStateEnum::labels()['unsubscribed'],
                'key'   => 'dispatched_emails_state_unsubscribed',
                'icon'  => DispatchedEmailStateEnum::stateIcon()['unsubscribed']['icon'],
                'class' => 'bg-red-200' ,
                'color' => 'red',
                'value' => $stats->number_dispatched_emails_state_unsubscribed,
            ],
            [
                'label' => DispatchedEmailStateEnum::labels()['ready'],
                'key'   => 'dispatched_emails_state_ready',
                'icon'  => DispatchedEmailStateEnum::stateIcon()['ready']['icon'],
                'class' => 'bg-red-200' ,
                'color' => 'rgb(59 130 246)',
                'value' => $stats->number_dispatched_emails_state_ready,
            ],
            [
                'label' => DispatchedEmailStateEnum::labels()['sent_to_provider'],
                'key'   => 'dispatched_emails_state_sent_to_provider',
                'icon'  => DispatchedEmailStateEnum::stateIcon()['sent_to_provider']['icon'],
                'class' => 'bg-red-200' ,
                'color' => 'rgb(59 130 246)',
                'value' => $stats->number_dispatched_emails_state_sent_to_provider,
            ],
            [
                'label' => DispatchedEmailStateEnum::labels()['rejected_by_provider'],
                'key'   => 'dispatched_emails_state_rejected_by_provider',
                'icon'  => DispatchedEmailStateEnum::stateIcon()['rejected_by_provider']['icon'],
                'class' => 'bg-red-200' ,
                'color' => 'rgb(59 130 246)',
                'value' => $stats->number_dispatched_emails_state_rejected_by_provider,
            ],
            [
                'label' => DispatchedEmailStateEnum::labels()['hard-bounce'],
                'key'   => 'dispatched_emails_state_delivered',
                'icon'  => DispatchedEmailStateEnum::stateIcon()['hard-bounce']['icon'],
                'class' => 'bg-red-200' ,
                'color' => 'rgb(59 130 34)',
                'value' => $stats->number_dispatched_emails_state_hard_bounce,
            ],
        ];
        return [
                'state' => $outbox->state,
                'builder' => $outbox->builder,
                'compiled_layout' => $outbox->emailOngoingRun?->email?->liveSnapshot?->compiled_layout,
                'stats' => $stats
        ];
    }
}
