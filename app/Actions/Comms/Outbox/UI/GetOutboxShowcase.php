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
        $dispatchedEmailStats = [
            [
                'label' => DispatchedEmailStateEnum::labels()['ready'],
                'key'   => 'dispatched_emails_state_ready',
                'icon'  => DispatchedEmailStateEnum::stateIcon()['ready']['icon'],
                'class' => DispatchedEmailStateEnum::stateIcon()['ready']['class'] ?? '',
                'value' => $stats->number_dispatched_emails_state_ready,
            ],
            [
                'label' => DispatchedEmailStateEnum::labels()['sent_to_provider'],
                'key'   => 'dispatched_emails_state_sent_to_provider',
                'icon'  => DispatchedEmailStateEnum::stateIcon()['sent_to_provider']['icon'],
                'class' => DispatchedEmailStateEnum::stateIcon()['sent_to_provider']['class'] ?? '',
                'value' => $stats->number_dispatched_emails_state_sent_to_provider,
            ],
            [
                'label' => DispatchedEmailStateEnum::labels()['rejected_by_provider'],
                'key'   => 'dispatched_emails_state_rejected_by_provider',
                'icon'  => DispatchedEmailStateEnum::stateIcon()['rejected_by_provider']['icon'],
                'class' => DispatchedEmailStateEnum::stateIcon()['rejected_by_provider']['class'] ?? '',
                'value' => $stats->number_dispatched_emails_state_rejected_by_provider,
            ],
            [
                'label' => DispatchedEmailStateEnum::labels()['sent'],
                'key'   => 'dispatched_emails_state_sent',
                'icon'  => DispatchedEmailStateEnum::stateIcon()['sent']['icon'],
                'class' => DispatchedEmailStateEnum::stateIcon()['sent']['class'] ?? '',
                'value' => $stats->number_dispatched_emails_state_sent,
            ],
            [
                'label' => DispatchedEmailStateEnum::labels()['delivered'],
                'key'   => 'dispatched_emails_state_delivered',
                'icon'  => DispatchedEmailStateEnum::stateIcon()['delivered']['icon'],
                'class' => DispatchedEmailStateEnum::stateIcon()['delivered']['class'] ?? '',
                'value' => $stats->number_dispatched_emails_state_delivered,
            ],
            [
                'label' => DispatchedEmailStateEnum::labels()['hard-bounce'],
                'key'   => 'dispatched_emails_state_delivered',
                'icon'  => DispatchedEmailStateEnum::stateIcon()['hard-bounce']['icon'],
                'class' => DispatchedEmailStateEnum::stateIcon()['hard-bounce']['class'] ?? '',
                'value' => $stats->number_dispatched_emails_state_hard_bounce,
            ],
            [
                'label' => DispatchedEmailStateEnum::labels()['soft-bounce'],
                'key'   => 'dispatched_emails_state_delivered',
                'icon'  => DispatchedEmailStateEnum::stateIcon()['soft-bounce']['icon'],
                'class' => DispatchedEmailStateEnum::stateIcon()['soft-bounce']['class'] ?? '',
                'value' => $stats->number_dispatched_emails_state_delivereds,
            ],
            [
                'label' => DispatchedEmailStateEnum::labels()['delivered'],
                'key'   => 'dispatched_emails_state_soft_bounce',
                'icon'  => DispatchedEmailStateEnum::stateIcon()['delivered']['icon'],
                'class' => DispatchedEmailStateEnum::stateIcon()['delivered']['class'] ?? '',
                'value' => $stats->number_dispatched_emails_state_soft_bounce,
            ],
            [
                'label' => DispatchedEmailStateEnum::labels()['opened'],
                'key'   => 'dispatched_emails_state_opened',
                'icon'  => DispatchedEmailStateEnum::stateIcon()['opened']['icon'],
                'class' => DispatchedEmailStateEnum::stateIcon()['opened']['class'] ?? '',
                'value' => $stats->number_dispatched_emails_state_opened,
            ],
            [
                'label' => DispatchedEmailStateEnum::labels()['clicked'],
                'key'   => 'dispatched_emails_state_clicked',
                'icon'  => DispatchedEmailStateEnum::stateIcon()['clicked']['icon'],
                'class' => DispatchedEmailStateEnum::stateIcon()['clicked']['class'] ?? '',
                'value' => $stats->number_dispatched_emails_state_clicked,
            ],
            [
                'label' => DispatchedEmailStateEnum::labels()['spam'],
                'key'   => 'dispatched_emails_state_spam',
                'icon'  => DispatchedEmailStateEnum::stateIcon()['spam']['icon'],
                'class' => DispatchedEmailStateEnum::stateIcon()['spam']['class'] ?? '',
                'value' => $stats->number_dispatched_emails_state_spam,
            ],
            [
                'label' => DispatchedEmailStateEnum::labels()['unsubscribed'],
                'key'   => 'dispatched_emails_state_unsubscribed',
                'icon'  => DispatchedEmailStateEnum::stateIcon()['unsubscribed']['icon'],
                'class' => DispatchedEmailStateEnum::stateIcon()['unsubscribed']['class'] ?? '',
                'value' => $stats->number_dispatched_emails_state_unsubscribed,
            ],
        ];
        return [
            [
                'state' => $outbox->state,
                'subscribers'                                   => $stats->number_subscribers,
                'unsubscribed'                                  => $stats->number_unsubscribed,
                'mailshots'                                     => $stats->number_mailshots,
                'email_bulk_runs'                               => $stats->number_email_bulk_runs,
                'email_ongoing_runs'                            => $stats->number_email_ongoing_runs,
                'dispatched_emails'                             => $stats->number_dispatched_emails,
                'dispacthed_email_stats'                        => $dispatchedEmailStats
            ]
        ];
    }
}
