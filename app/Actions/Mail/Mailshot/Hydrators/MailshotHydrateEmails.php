<?php
/*
 * author Arya Permana - Kirin
 * created on 13-11-2024-13h-43m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Mail\Mailshot\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Mail\DispatchedEmail\DispatchedEmailStateEnum;
use App\Models\Mail\DispatchedEmail;
use App\Models\Mail\Mailshot;
use App\Models\Mail\Outbox;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class MailshotHydrateEmails
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
        $stats = [
            'number_dispatched_emails' => $mailshot->dispatchedEmails()->count()
        ];

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'dispatched_emails',
                field: 'state',
                enum: DispatchedEmailStateEnum::class,
                models: DispatchedEmail::class,
                where: function ($q) use ($mailshot) {
                    $q->where('mailshot_id', $mailshot->id);
                }
            )
        );

        $mailshot->stats()->update($stats);
    }
}
