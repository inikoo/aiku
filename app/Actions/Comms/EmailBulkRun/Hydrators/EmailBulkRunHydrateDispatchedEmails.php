<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:35 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\EmailBulkRun\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Comms\DispatchedEmail\DispatchedEmailStateEnum;
use App\Models\Comms\DispatchedEmail;
use App\Models\Comms\EmailBulkRun;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class EmailBulkRunHydrateDispatchedEmails
{
    use AsAction;
    use WithEnumStats;

    private EmailBulkRun $emailBulkRun;

    public function __construct(EmailBulkRun $emailBulkRun)
    {
        $this->emailBulkRun = $emailBulkRun;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->emailBulkRun->id))->dontRelease()];
    }


    public function handle(EmailBulkRun $emailBulkRun): void
    {
        $stats = [
            'number_dispatched_emails' => $emailBulkRun->dispatchedEmails()->count()
        ];

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'dispatched_emails',
                field: 'state',
                enum: DispatchedEmailStateEnum::class,
                models: DispatchedEmail::class,
                where: function ($q) use ($emailBulkRun) {
                    $q->where('emailBulkRun_id', $emailBulkRun->id);
                }
            )
        );

        $emailBulkRun->stats()->update($stats);
    }
}
