<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 30 Jun 2023 10:29:12 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Notifications;

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
                $subject     = $message['Subject'];
                $messageData = json_decode($message['Message']);
                // use $subject and $messageData and take relevant action
            }
        }
    }
}
