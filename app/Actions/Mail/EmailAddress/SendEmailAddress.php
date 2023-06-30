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

    public function handle(array $content, string|array $to, $attach = null, $type = 'html'): void
    {
        Mail::$type($content['body'], function ($message) use ($to, $content, $attach) {
            $this->message = $message;
            $this->attachments($attach);

            $message->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
                ->to($to)
                ->subject($content['title']);
        });
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
