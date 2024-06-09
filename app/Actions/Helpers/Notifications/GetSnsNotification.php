<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 13:32:39 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Notifications;

use App\Actions\Mail\DispatchedEmail\UpdateDispatchedEmail;
use App\Models\Mail\DispatchedEmail;
use App\Models\SysAdmin\Organisation;
use Aws\Sns\Message;
use Aws\Sns\MessageValidator;
use Lorisleiva\Actions\Concerns\AsAction;

class GetSnsNotification
{
    use AsAction;

    public function asController(): void
    {
        $message   = Message::fromRawPostData();
        $validator = new MessageValidator();

        if ($validator->isValid($message)) {
            if ($message['Type'] == 'SubscriptionConfirmation') {
                file_get_contents($message['SubscribeURL']);
            } elseif ($message['Type'] === 'Notification') {
                $messageData = json_decode($message['Message'], true);

                $messageId = $messageData['mail']['messageId'];
                $timestamp = $messageData['mail']['timestamp'];

                $currentTenant =  explode('.', explode('@', $messageData['mail']['source'])[1])[0];

                Organisation::where('slug', $currentTenant)->first()->makeCurrent();

                $dispatchedEmail = DispatchedEmail::where('ses_id', $messageId)->first();
                UpdateDispatchedEmail::run($dispatchedEmail, [
                    'first_read_at' => $timestamp,
                    'last_read_at'  => $timestamp
                ]);
            }
        }
    }
}
