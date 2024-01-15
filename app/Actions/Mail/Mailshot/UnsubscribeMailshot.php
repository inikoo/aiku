<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 14 Dec 2023 23:21:39 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Mailshot;

use App\Actions\Leads\Prospect\UpdateProspectEmailUnsubscribed;
use App\Actions\Mail\DispatchedEmail\UpdateDispatchedEmail;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Mail\DispatchedEmail\DispatchedEmailStateEnum;
use App\Enums\Mail\DispatchedEmailEvent\DispatchedEmailEventTypeEnum;
use App\Http\Resources\Mail\DispatchedEmailResource;
use App\Models\Mail\DispatchedEmail;
use Lorisleiva\Actions\ActionRequest;

class UnsubscribeMailshot
{
    use WithActionUpdate;

    public function handle(DispatchedEmail $dispatchedEmail, ActionRequest $request): DispatchedEmail
    {
        if ($dispatchedEmail->is_test) {
            return $dispatchedEmail;
        }

        $recipient = $dispatchedEmail->mailshotRecipient->recipient;
        if (class_basename($recipient) == 'Prospect') {
            UpdateProspectEmailUnsubscribed::run($recipient, now());
        }

        UpdateDispatchedEmail::run(
            $dispatchedEmail,
            [
                'state'           => DispatchedEmailStateEnum::UNSUBSCRIBED,
                'date'            => now(),
                'is_unsubscribed' => true

            ]
        );

        $eventData = [
            'type' => DispatchedEmailEventTypeEnum::UNSUBSCRIBE,
            'date' => now(),
            'data' => [
                'ipAddress' => $request->ip(),
                'userAgent' => $request->userAgent()
            ]
        ];


        $dispatchedEmail->events()->create($eventData);


        return $this->update($dispatchedEmail, ['state' => DispatchedEmailStateEnum::UNSUBSCRIBED]);
    }

    public function asController(DispatchedEmail $dispatchedEmail, ActionRequest $request): DispatchedEmail
    {
        return $this->handle($dispatchedEmail, $request);
    }

    public function jsonResponse(DispatchedEmail $dispatchedEmail): array
    {
        return DispatchedEmailResource::make($dispatchedEmail)->getArray();
    }

}
