<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Nov 2023 15:13:41 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Mail;

use App\Http\Resources\HasSelfCall;
use App\Models\Mail\MailshotStats;
use Illuminate\Http\Resources\Json\JsonResource;

class MailshotStatResource extends JsonResource
{
    use HasSelfCall;

    public function toArray($request): array
    {
        /** @var MailshotStats $mailshotStatsStats */
        $mailshotStatsStats = $this;

        return [
            'number_estimated_dispatched_emails' => $mailshotStatsStats->number_estimated_dispatched_emails,
            'number_dispatched_emails'           => $mailshotStatsStats->number_dispatched_emails,
            'number_error_emails'                => $mailshotStatsStats->number_error_emails,
            'number_rejected_emails'             => $mailshotStatsStats->number_rejected_emails,
            'number_sent_emails'                 => $mailshotStatsStats->number_sent_emails,
            'number_delivered_emails'            => $mailshotStatsStats->number_delivered_emails,
            'number_hard_bounced_emails'         => $mailshotStatsStats->number_hard_bounced_emails,
            'number_soft_bounced_emails'         => $mailshotStatsStats->number_soft_bounced_emails,
            'number_opened_emails'               => $mailshotStatsStats->number_opened_emails,
            'number_clicked_emails'              => $mailshotStatsStats->number_clicked_emails,
            'number_spam_emails'                 => $mailshotStatsStats->number_spam_emails,
            'number_unsubscribed_emails'         => $mailshotStatsStats->number_unsubscribed_emails,

        ];
    }
}
