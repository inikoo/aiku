<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 12 Mar 2023 19:59:43 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mailroom\EmailTrackingEvent;

use App\Models\Mailroom\DispatchedEmail;
use App\Models\Mailroom\EmailTrackingEvent;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreEmailTrackingEvent
{
    use AsAction;

    public function handle(DispatchedEmail $dispatchedEmail, array $modelData): EmailTrackingEvent
    {
        /** @var EmailTrackingEvent $emailTrackingEvent */
        $emailTrackingEvent= $dispatchedEmail->emailTrackingEvents()->create($modelData);
        return $emailTrackingEvent;
    }
}
