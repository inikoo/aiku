<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Jan 2025 20:10:46 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\WebUserPasswordReset;

use App\Models\CRM\WebUser;
use App\Models\CRM\WebUserPasswordReset;
use Hash;
use Lorisleiva\Actions\Concerns\AsObject;

class StoreWebUserPasswordReset
{
    use AsObject;

    public function handle(WebUser $webUser, string $token): WebUserPasswordReset
    {
        data_set($modelData, 'website_id', $webUser->website_id);
        data_set($modelData, 'token', Hash::make($token));

        return $webUser->passwordResets()->create($modelData);
    }


}
