<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 29 Sep 2023 10:17:48 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\WebUser;

use App\Actions\Comms\Email\SendResetPasswordEmail;
use App\Models\CRM\WebUser;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class SendLinkResetPassword
{
    use AsAction;

    public function handle(string $token, WebUser $webUser): void
    {
        $url = route('retina.email.reset-password.show', [
            'token' => $token,
            'email' => $webUser->email
        ]);


        SendResetPasswordEmail::run($webUser, [
            'url' => $url
        ]);
    }

}
