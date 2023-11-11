<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 05 May 2023 10:24:56 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Organisation\Organisation;

use App\Actions\Procurement\Agent\Hydrators\AgentHydrateUniversalSearch;
use App\Actions\Organisation\Organisation\Hydrators\OrganisationHydrateProcurement;
use App\Models\Procurement\Agent;
use App\Models\Organisation\Organisation;
use Lorisleiva\Actions\Concerns\AsAction;

class AttachAgent
{
    use AsAction;

    public function handle(Organisation $organisation, Agent $agent, array $pivotData = []): Organisation
    {
        return $organisation->execute(function (Organisation $organisation) use ($agent, $pivotData) {
            $organisation->agents()->attach($agent, $pivotData);
            OrganisationHydrateProcurement::dispatch($organisation);
            AgentHydrateUniversalSearch::dispatch($agent);

            foreach ($agent->products as $product) {
                AttachSupplierProduct::run($organisation, $product);
            }

            return $organisation;
        });
    }

}
