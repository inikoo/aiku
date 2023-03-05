<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 15 Oct 2022 19:09:22 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Central\CentralDomain;

use App\Actions\SysAdmin\AdminUser\StoreAdminUser;
use App\Models\Central\CentralDomain;
use App\Models\Central\Tenant;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreCentralDomain
{
    use AsAction;

    public function handle(Tenant $tenant, array $modelData): CentralDomain
    {
        /** @var CentralDomain $centralDomain */
        $centralDomain = $tenant->centralDomains()->create($modelData);
        $centralDomain->stats()->create();
        StoreAdminUser::run(
            $centralDomain,
            [
                'username'=> 'iris-'.$centralDomain->slug,
                'password'=> wordwrap(Str::random(), 4, '-', true)
            ]
        );
        SetIrisDomain::run($centralDomain);

        return $centralDomain;
    }
}
