<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 20 Feb 2025 13:37:24 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\DispatchedEmail\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Comms\EmailTrackingEvent\EmailTrackingEventTypeEnum;
use App\Models\Comms\DispatchedEmail;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class DispatchedEmailHydrateEmailTracking
{
    use AsAction;
    use WithEnumStats;

    private DispatchedEmail $dispatchedEmail;

    public function __construct(DispatchedEmail $dispatchedEmail)
    {
        $this->dispatchedEmail = $dispatchedEmail;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->dispatchedEmail->id))->dontRelease()];
    }


    public function handle(DispatchedEmail $dispatchedEmail): void
    {
        $stats = [
            'number_clicks' => $dispatchedEmail
                ->emailTrackingEvents()
                ->where('type', EmailTrackingEventTypeEnum::CLICKED)
                ->count(),
            'number_reads' => $dispatchedEmail
                ->emailTrackingEvents()
                ->where('type', EmailTrackingEventTypeEnum::OPENED)
                ->count()
        ];

        $dispatchedEmail->update($stats);
    }
}
