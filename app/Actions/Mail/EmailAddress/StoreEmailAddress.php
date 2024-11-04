<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Mar 2023 01:05:45 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\EmailAddress;

use App\Models\Mail\EmailAddress;
use App\Models\SysAdmin\Group;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreEmailAddress
{
    use AsAction;

    public function handle(Group $group, string $email): EmailAddress
    {
        $emailAddress = $group->emailAddresses()->where('email', $email)->first();
        if (!$emailAddress) {
            $emailAddress = $group->emailAddresses()->create(
                [
                    'email' => $email
                ]
            );
        }

        return $emailAddress;
    }
}
