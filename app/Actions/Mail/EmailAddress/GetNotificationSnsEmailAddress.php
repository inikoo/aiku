<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Mar 2023 01:05:45 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\EmailAddress;

use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class GetNotificationSnsEmailAddress
{
    use AsAction;

    public mixed $message;

    public function handle(mixed $request)
    {
        $snsMessage = json_decode($request, true);

        SendEmailAddress::run(['name' => 'AWA', 'email' => 'aw@aiku-devels.uk'], $snsMessage, "SNS MESSAGE CONFIRM", 'dev@aw-advantage.com');

        return $snsMessage;
    }

    public function asController(ActionRequest $request)
    {
        SendEmailAddress::run('aiku@aiku-devels.uk', '', "SNS MESSAGE CONFIRM", 'dev@aw-advantage.com');

        return $this->handle($request->getContent());
    }
}
