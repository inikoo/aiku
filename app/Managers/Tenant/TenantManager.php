<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 23 Aug 2022 02:51:52 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Managers\Tenant;

use App\Services\Tenant\AuroraTenantService;
use App\Services\Tenant\SourceTenantService;
use Exception;
use Illuminate\Support\Arr;

class TenantManager implements SourceTenantManager{

    private array $tenants = [];



    /**
     * @throws \Exception
     * @uses createAuroraTenantService
     */
    public function make($name): SourceTenantService
    {
        $service = Arr::get($this->tenants, $name);
        if ($service) {
            return $service;
        }

        $createMethod = 'create' . ucfirst($name) . 'TenantService';
        if (!method_exists($this, $createMethod)) {
            throw new Exception("Tenant source $name is not supported");
        }
        $service = $this->{$createMethod}();
        $this->tenants[$name] = $service;
        return $service;

    }

    private function createAuroraTenantService(): AuroraTenantService
    {
        return new AuroraTenantService();
    }

}

