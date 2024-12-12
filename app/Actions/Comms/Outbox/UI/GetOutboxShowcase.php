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
        return [
            [
                'state' => $outbox->state,
                'subscribers'                                   => $stats->number_subscribers,
                'unsubscribed'                                  => $stats->number_unsubscribed,
                'mailshots'                                     => $stats->number_mailshots,
                'email_bulk_runs'                               => $stats->number_email_bulk_runs,
                'email_ongoing_runs'                            => $stats->number_email_ongoing_runs,
                'dispatched_emails'                             => $stats->number_dispatched_emails,
                'dispatched_emails_state_ready'                 => [
                    'label' => DispatchedEmailStateEnum::labels()['ready'],
                    'value' => $stats->number_dispatched_emails_state_ready,
                    'icon'  => DispatchedEmailStateEnum::stateIcon()['ready']
                ],
                'dispatched_emails_state_sent_to_provider'      => [
                    'label' => DispatchedEmailStateEnum::labels()['sent_to_provider'],
                    'value' => $stats->number_dispatched_emails_state_sent_to_provider,
                    'icon'  => DispatchedEmailStateEnum::stateIcon()['sent_to_provider']
                ],
                'dispatched_emails_state_rejected_by_provider'  => [
                    'label' => DispatchedEmailStateEnum::labels()['rejected_by_provider'],
                    'value' => $stats->number_dispatched_emails_state_rejected_by_provider,
                    'icon'  => DispatchedEmailStateEnum::stateIcon()['rejected_by_provider']
                ],
                'dispatched_emails_state_sent'                  => [
                    'label' => DispatchedEmailStateEnum::labels()['sent'],
                    'value' => $stats->number_dispatched_emails_state_sent,
                    'icon'  => DispatchedEmailStateEnum::stateIcon()['sent']
                ],
                'dispatched_emails_state_delivered'             => [
                    'label' => DispatchedEmailStateEnum::labels()['delivered'],
                    'value' => $stats->number_dispatched_emails_state_ready,
                    'icon'  => DispatchedEmailStateEnum::stateIcon()['delivered']
                ],
                'dispatched_emails_state_hard_bounce'           => [
                    'label' => DispatchedEmailStateEnum::labels()['hard-bounce'],
                    'value' => $stats->number_dispatched_emails_state_hard_bounce,
                    'icon'  => DispatchedEmailStateEnum::stateIcon()['hard-bounce']
                ],
                'dispatched_emails_state_soft_bounce'           => [
                    'label' => DispatchedEmailStateEnum::labels()['soft-bounce'],
                    'value' => $stats->number_dispatched_emails_state_soft_bounce,
                    'icon'  => DispatchedEmailStateEnum::stateIcon()['soft-bounce']
                ],
                'dispatched_emails_state_opened'                => [
                    'label' => DispatchedEmailStateEnum::labels()['opened'],
                    'value' => $stats->number_dispatched_emails_state_opened,
                    'icon'  => DispatchedEmailStateEnum::stateIcon()['opened']
                ],
                'dispatched_emails_state_clicked'               => [
                    'label' => DispatchedEmailStateEnum::labels()['clicked'],
                    'value' => $stats->number_dispatched_emails_state_clicked,
                    'icon'  => DispatchedEmailStateEnum::stateIcon()['clicked']
                ],
                'dispatched_emails_state_spam'                  => [
                    'label' => DispatchedEmailStateEnum::labels()['spam'],
                    'value' => $stats->number_dispatched_emails_state_spam,
                    'icon'  => DispatchedEmailStateEnum::stateIcon()['spam']
                ],
                'dispatched_emails_state_unsubscribed'          => [
                    'label' => DispatchedEmailStateEnum::labels()['unsubscribed'],
                    'value' => $stats->number_dispatched_emails_state_unsubscribed,
                    'icon'  => DispatchedEmailStateEnum::stateIcon()['unsubscribed']
                ],
            ]
        ];
    }
}
