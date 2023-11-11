<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 02 Jul 2023 15:42:35 Malaysia Time, Sanur, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Tasks;

use App\Models\Organisation\TenantPersonalAccessToken;
use Laravel\Sanctum\Sanctum;
use Spatie\Multitenancy\Models\Tenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;
use Laravel\Sanctum\PersonalAccessToken;

class SetSanctumPersonAccessTokenModelTask implements SwitchTenantTask
{
    public function makeCurrent(Tenant $organisation): void
    {
        Sanctum::usePersonalAccessTokenModel(TenantPersonalAccessToken::class);
    }

    public function forgetCurrent(): void
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
    }
}
