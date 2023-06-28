<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sat, 15 Oct 2022 19:09:22 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Central\Central;

use App\Actions\SysAdmin\SysUser\StoreSysUser;
use App\Models\Central\Domain;
use App\Models\Tenancy\Tenant;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreDomain
{
    use AsAction;

    public function handle(Tenant $tenant, array $modelData): Domain
    {
        /** @var Domain $domain */
        $domain = $tenant->domains()->create($modelData);
        $domain->stats()->create();
        StoreSysUser::run(
            $domain,
            [
                'username'=> 'iris-'.$domain->slug,
                'password'=> wordwrap(Str::random(), 4, '-', true)
            ]
        );
        SetIrisDomain::run($domain);
        AddDomainToCloudflare::run($domain);

        return $domain;
    }
}
