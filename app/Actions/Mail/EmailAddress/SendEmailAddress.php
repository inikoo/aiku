<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Mar 2023 01:05:45 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\EmailAddress;

use App\Actions\Mail\DispatchedEmail\StoreDispatchEmail;
use App\Actions\Mail\Ses\SendSesEmail;
use App\Models\Mail\Outbox;
use Lorisleiva\Actions\Concerns\AsAction;

class SendEmailAddress
{
    use AsAction;

    public mixed $message;

    public function handle(array $content, string $to, $attach = null, $type = 'html'): void
    {
        $emailAddress = GetEmailAddress::run($to);
        $response = SendSesEmail::run($content, $emailAddress->email, $attach, $type);

        $modelData = [
            'ses_id' => $response['MessageId'],
            'sent_at' => now()
        ];

        $outbox = Outbox::find(1); // TODO U need implement the real one, this just for test

        StoreDispatchEmail::run($outbox, $emailAddress->email, $modelData);
    }

    public function attachments(array|string|null $attachments)
    {
        if(is_array($attachments)) {
            foreach ($attachments as $attach) {
                $this->message->attach($attach);
            }
        }

        if(is_string($attachments)) {
            $this->message->attach($attachments);
        }

        return $this->message;
    }
}
