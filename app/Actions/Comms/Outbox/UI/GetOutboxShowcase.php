<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:35 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\Outbox\UI;

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

                'stats' => [
                    'subscribers'               => $stats->number_subscribers,
                    'unsubscribed'              => $stats->number_unsubscribed,
                    'mailshots'                 => $stats->number_mailshots,
                    'email_bulk_runs'           => $stats->number_email_bulk_runs,
                    'email_ongoing_runs'        => $stats->number_email_ongoing_runs,
                    'dispatched_emails'         => [
                            'total'                 => $stats->number_dispatched_emails,
                            'ready'                 => $stats->number_dispatched_emails_state_ready,
                            'sent_to_provider'      => $stats->number_dispatched_emails_state_sent_to_provider,
                            'rejected_by_provider'  => $stats->number_dispatched_emails_state_rejected_by_provider,
                            'sent'                  => $stats->number_dispatched_emails_state_sent,
                            'delivered'             => $stats->number_dispatched_emails_state_delivered,
                            'hard_bounce'           => $stats->number_dispatched_emails_state_hard_bounce,
                            'soft_bounce'           => $stats->number_dispatched_emails_state_soft_bounce,
                            'opened'                => $stats->number_dispatched_emails_state_opened,
                            'clicked'               => $stats->number_dispatched_emails_state_clicked,
                            'spam'                  => $stats->number_dispatched_emails_state_spam,
                            'unsubscribed'          => $stats->number_dispatched_emails_state_unsubscribed,
                    ]
                ]
            ]
        ];
    }
}
