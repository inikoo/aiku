<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Nov 2023 14:41:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Mailshot\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Mail\DispatchedEmail\DispatchedEmailStateEnum;
use App\Events\MailshotPusherEvent;
use App\Models\Mail\DispatchedEmail;
use App\Models\Mail\Mailshot;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class MailshotHydrateDispatchedEmailsState
{
    use AsAction;
    use WithEnumStats;

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
        $stats = $this->getEnumStats(
            model: 'dispatched_emails',
            field: 'state',
            enum: DispatchedEmailStateEnum::class,
            models: DispatchedEmail::class,
            where: function ($q) use ($mailshot) {
                $q->where('mailshot_id', $mailshot->id)->where('is_test', false);
            }
        );
        $mailshot->mailshotStats()->update($stats);

        MailshotPusherEvent::dispatch($mailshot);

    }


}
