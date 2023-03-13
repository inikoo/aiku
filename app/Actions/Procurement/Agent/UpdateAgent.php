<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 25 Oct 2022 11:01:21 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Agent;

use App\Actions\Procurement\Agent\Hydrators\AgentHydrateUniversalSearch;
use App\Actions\WithActionUpdate;
use App\Models\Procurement\Agent;

class UpdateAgent
{
    use WithActionUpdate;

    public function handle(Agent $agent, array $modelData): Agent
    {
        $agent = $this->update($agent, $modelData, ['shared_data','tenant_data', 'settings']);
        AgentHydrateUniversalSearch::dispatch($agent);
        return $agent;
    }
}
