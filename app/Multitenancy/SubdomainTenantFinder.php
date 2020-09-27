<?php
/*
Author: Raul A Perusquía-Flores (raul@inikoo.com)
Created:  Sat Aug 01 2020 22:58:20 GMT+0800 (Malaysia Time) Kuala Lumpur, Malaysia
Copyright (c) 2020,  AIku.io

Version 4
*/


namespace App\Multitenancy;

use Illuminate\Http\Request;
use Spatie\Multitenancy\Models\Concerns\UsesTenantModel;
use Spatie\Multitenancy\Models\Tenant;
use Spatie\Multitenancy\TenantFinder\TenantFinder;

class SubdomainTenantFinder extends TenantFinder
{
    use UsesTenantModel;

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Spatie\Multitenancy\Models\Tenant|null
     */
    public function findForRequest(Request $request):?Tenant
    {

        $host=$request->getHost();
        if($host==env('APP_DOMAIN')){
            return null;
        }

        list($subdomain) = explode('.', $host, 2);

        /** @noinspection PhpUndefinedMethodInspection */
        return $this->getTenantModel()::where('subdomain', $subdomain)->first();



    }
}
