<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 21 Feb 2022 23:37:01 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Resolvers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Cookie;
use Spatie\Multitenancy\Models\Concerns\UsesTenantModel;
use Spatie\Multitenancy\Models\Tenant;
use Spatie\Multitenancy\TenantFinder\TenantFinder;

class TenantResolver extends TenantFinder
{
    use UsesTenantModel;

    public function findForRequest(Request $request): ?Tenant
    {
        $subdomain = current(explode('.', $request->getHost()));
        if (in_array($subdomain, ['www', 'aiku'])) {
            return null;
        }
        if (in_array($subdomain, ['app','agents'])) {
            if ($request->hasCookie('tenant')) {
                $tenant = $this->getTenantModel()->find(decrypt($request->cookie('tenant')));

                if (!empty($tenant)) {
                    return $tenant;
                }
                Cookie::forget('tenant');
            }
            return null;
        }

        return $this->getTenantModel()->where('slug', $subdomain)->first();
    }
}
