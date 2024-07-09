<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 18 Jan 2024 17:14:23 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\Agent\Hydrators;

use App\Models\SupplyChain\Agent;
use Lorisleiva\Actions\Concerns\AsAction;

class AgentHydrateUniversalSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(Agent $agent): void
    {
        $agent->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'       => $agent->group_id,
                'section'        => 'procurement',
                'title'          => trim($agent->organisation->name.' '.$agent->organisation->email.' '.$agent->organisation->phone),
                'description'    => ''
            ]
        );
    }

}
