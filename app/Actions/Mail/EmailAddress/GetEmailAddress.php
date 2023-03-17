<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Mar 2023 01:05:45 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mailroom\EmailAddress;

use App\Models\Mail\EmailAddress;

use Lorisleiva\Actions\Concerns\AsAction;

class GetEmailAddress
{
    use AsAction;

    public function handle(string $email): EmailAddress
    {
        $emailAddress = EmailAddress::where('email', $email)->first();
        if (!$emailAddress) {
            $emailAddress = EmailAddress::create(
                [
                    'email' => $email
                ]
            );
        }

        return $emailAddress;
    }
}
