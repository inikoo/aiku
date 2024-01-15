<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 09 Nov 2023 18:44:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Mailshot\Hydrators;

use App\Actions\Mail\Mailshot\GetEstimatedNumberRecipients;
use App\Events\MailshotPusherEvent;
use App\Models\Mail\Mailshot;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class MailshotHydrateEstimatedEmails
{
    use AsAction;

    private Mailshot $mailshot;

    public function __construct(Mailshot $mailshot)
    {
        $this->mailshot = $mailshot;
    }


    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->mailshot->id))->dontRelease()];
    }

    public function handle(Mailshot $mailshot): void
    {

        $estimatedNumberRecipients = GetEstimatedNumberRecipients::run(
            $mailshot->parent,
            $mailshot->recipients_recipe
        );

        $mailshot->mailshotStats()->update(
            [
                'number_estimated_dispatched_emails'        => $estimatedNumberRecipients,
                'estimated_dispatched_emails_calculated_at' => now()
            ]
        );

        $mailshot->refresh();
        MailshotPusherEvent::dispatch($mailshot);
    }


}
