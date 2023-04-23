<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 07 Sept 2022 17:20:22 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\AdminUser;

use App\Models\Assets\Language;
use App\Models\Assets\Timezone;
use App\Models\Central\CentralDomain;
use App\Models\SysAdmin\Admin;
use App\Models\SysAdmin\AdminUser;
use App\Models\Tenancy\Tenant;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreAdminUser
{
    use AsAction;


    public function handle(Admin|Tenant|CentralDomain $userable, array $userData): AdminUser
    {
        if (empty($userData['language_id'])) {
            $language                = Language::where('code', config('app.locale'))->firstOrFail();
            $userData['language_id'] = $language->id;
        }

        if (empty($userData['timezone_id'])) {
            $timezone                = Timezone::where('name', config('app.timezone'))->firstOrFail();
            $userData['timezone_id'] = $timezone->id;
        }

        /** @var \App\Models\SysAdmin\AdminUser $adminUser */
        $adminUser = $userable->adminUser()->create($userData);
        return $adminUser;
    }
}
