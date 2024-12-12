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
                'subscribers'                                   => $stats->number_subscribers,
                'unsubscribed'                                  => $stats->number_unsubscribed,
                'mailshots'                                     => $stats->number_mailshots,
                'email_bulk_runs'                               => $stats->number_email_bulk_runs,
                'email_ongoing_runs'                            => $stats->number_email_ongoing_runs,
                'dispatched_emails'                             => $stats->number_dispatched_emails,
                'dispatched_emails_state_ready'                 => $stats->number_dispatched_emails_state_ready,
                'dispatched_emails_state_sent_to_provider'      => $stats->number_dispatched_emails_state_sent_to_provider,
                'dispatched_emails_state_rejected_by_provider'  => $stats->number_dispatched_emails_state_rejected_by_provider,
                'dispatched_emails_state_sent'                  => $stats->number_dispatched_emails_state_sent,
                'dispatched_emails_state_delivered'             => $stats->number_dispatched_emails_state_delivered,
                'dispatched_emails_state_hard_bounce'           => $stats->number_dispatched_emails_state_hard_bounce,
                'dispatched_emails_state_soft_bounce'           => $stats->number_dispatched_emails_state_soft_bounce,
                'dispatched_emails_state_opened'                => $stats->number_dispatched_emails_state_opened,
                'dispatched_emails_state_clicked'               => $stats->number_dispatched_emails_state_clicked,
                'dispatched_emails_state_spam'                  => $stats->number_dispatched_emails_state_spam,
                'dispatched_emails_state_unsubscribed'          => $stats->number_dispatched_emails_state_unsubscribed,
            ]
        ];
    }
}
