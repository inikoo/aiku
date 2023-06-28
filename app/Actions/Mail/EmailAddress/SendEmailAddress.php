<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Mar 2023 01:05:45 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\EmailAddress;

use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;

class SendEmailAddress
{
    use AsAction;

    public mixed $message;

    public function handle(array $from, string $content, string $subject, string|array $to, $attach = null, $type = 'html'): void
    {
        Mail::$type($content, function ($message) use ($from, $to, $subject, $attach) {
            $this->message = $message;
            $this->attachments($attach);

            $message->from($from['email'], $from['name'])
                ->to($to)
                ->subject($subject);
        });
    }

    public function attachments(array|string $attachments)
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
