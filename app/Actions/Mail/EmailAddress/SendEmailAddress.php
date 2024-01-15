<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Mar 2023 01:05:45 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\EmailAddress;

use App\Actions\Mail\Ses\SendSesEmail;
use App\Models\Mail\Mailshot;
use Lorisleiva\Actions\Concerns\AsAction;

class SendEmailAddress
{
    use AsAction;

    public mixed $message;

    public function handle(Mailshot $mailshot): void
    {
        foreach ($mailshot->recipients as $recipient) {
            SendSesEmail::run($recipient);
        }
    }
}
