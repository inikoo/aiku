<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 05 May 2023 10:24:56 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Tenancy\Tenant;

use App\Actions\Procurement\Agent\Hydrators\AgentHydrateUniversalSearch;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateProcurement;
use App\Models\Procurement\Agent;
use App\Models\Tenancy\Tenant;
use Lorisleiva\Actions\Concerns\AsAction;

class AttachAgent
{
    use AsAction;

    public function handle(Tenant $tenant, Agent $agent, array $pivotData = []): Tenant
    {
        return $tenant->execute(function (Tenant $tenant) use ($agent, $pivotData) {
            $tenant->agents()->attach($agent, $pivotData);
            TenantHydrateProcurement::dispatch($tenant);
            AgentHydrateUniversalSearch::dispatch($agent);

            return $tenant;
        });
    }

}
