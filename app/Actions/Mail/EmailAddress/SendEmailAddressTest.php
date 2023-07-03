<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Mar 2023 01:05:45 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\EmailAddress;

use App\Actions\Mail\Ses\SendSesEmail;
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
            'body' => 'hello'
        ];

        SendSesEmail::run($content, $to,
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
