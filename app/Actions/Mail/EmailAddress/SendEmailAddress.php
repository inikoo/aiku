<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Mar 2023 01:05:45 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\EmailAddress;

use App\Mail\TestEmail;
use Illuminate\Console\Command;
use Illuminate\Mail\SentMessage;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;

class SendEmailAddress
{
    use AsAction;

    public string $commandSignature   = 'mail:send {to}';
    public string $commandDescription = 'Sending Email Test Email';

    public function handle(string $to): SentMessage
    {
        return Mail::to($to)->send(new TestEmail());
    }

    public function asCommand(Command $command): void
    {
        $this->handle($command->argument('to'));
    }
}
