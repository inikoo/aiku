<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Wed, 03 May 2023 13:41:44 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Agent;

use App\Actions\Procurement\Agent\Hydrators\AgentHydrateUniversalSearch;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Procurement\Agent;
use App\Models\Tenancy\Tenant;

class ChangeAgentOwner
{
    use WithActionUpdate;

    public function handle(Agent $agent, Tenant $tenant): Agent
    {
        $agent = $this->update($agent, [
            'owner_id' => $tenant->id
        ]);
        AgentHydrateUniversalSearch::dispatch($agent);

        return $agent;
    }
}
