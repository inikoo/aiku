<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 12 Jul 2023 13:31:30 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Traits;

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
