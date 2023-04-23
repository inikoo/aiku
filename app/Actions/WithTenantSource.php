<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 05 Mar 2023 17:58:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions;

use App\Models\Tenancy\Tenant;
use App\Services\Tenant\AuroraTenantService;
use Exception;
use Illuminate\Support\Arr;

trait WithTenantSource
{
    /**
     * @throws \Exception
     */
    public function getTenantSource(Tenant $tenant): AuroraTenantService
    {
        $sourceType = Arr::get($tenant->source, 'type');
        if (!$sourceType) {
            throw new Exception("Tenant dont have source");
        }

        $tenantSource = match (Arr::get($tenant->source, 'type')) {
            'Aurora' => new AuroraTenantService(),
            default  => null
        };

        if (!$tenantSource) {
            throw new Exception("Tenant source $sourceType is not supported");
        }

        return $tenantSource;
    }
}
