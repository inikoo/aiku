<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 19 Nov 2024 11:09:35 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Comms\EmailAddress;

use App\Actions\Comms\Ses\SendSesEmail;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class SendEmailAddressTest
{
    use AsAction;

    public string $commandSignature   = 'mail:send {to}';
    public string $commandDescription = 'Sending email test';

    public function handle(string $to): void
    {
        $content = [
            'title' => 'subject',
            'body'  => 'hello'
        ];

        SendSesEmail::run(
            $content,
            $to,
            [
                storage_path('app/public/devices/mobile.png'),
                storage_path('app/public/devices/desktop.png')
            ]
        );
    }

    public function asCommand(Command $command): void
    {
        $this->handle($command->argument('to'));
    }
}
