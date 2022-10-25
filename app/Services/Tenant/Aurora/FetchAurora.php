<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 29 Aug 2022 20:18:44 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Services\Tenant\Aurora;


use App\Models\Central\Tenant;
use App\Services\Tenant\SourceTenantService;


class FetchAurora
{
    use WithAuroraParsers;


    protected Tenant $tenant;
    protected array $parsedData;
    protected ?object $auroraModelData;
    protected SourceTenantService $tenantSource;

    function __construct(SourceTenantService $tenantSource)
    {
        $this->tenantSource    = $tenantSource;
        $this->tenant          = $tenantSource->tenant;
        $this->parsedData      = [];
        $this->auroraModelData = null;
    }

    public function fetch(int $id): ?array
    {
        $this->auroraModelData = $this->fetchData($id);

        if ($this->auroraModelData) {
            $this->parseModel();
        }

        return $this->parsedData;
    }

    protected function fetchData($id): object|null
    {
        return null;
    }

    protected function parseModel(): void
    {
    }

}
