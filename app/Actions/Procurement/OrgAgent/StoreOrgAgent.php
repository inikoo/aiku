<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 03 Apr 2024 20:54:46 Central Indonesia Time, Bali Office , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\OrgAgent;

use App\Actions\OrgAction;
use App\Models\Procurement\OrgAgent;
use App\Models\SupplyChain\Agent;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\ActionRequest;

class StoreOrgAgent extends OrgAction
{
    public function handle(Organisation $organisation, Agent $agent, $modelData = []): OrgAgent
    {
        data_set($modelData, 'group_id', $organisation->group_id);
        data_set($modelData, 'organisation_id', $organisation->id);
        data_set($modelData, 'status', $agent->status, false);


        /** @var OrgAgent $orgAgent */
        $orgAgent = $agent->orgAgents()->create($modelData);
        $orgAgent->stats()->create();


        return $orgAgent;
    }


    public function rules(ActionRequest $request): array
    {
        return [
            'source_id' => 'sometimes|nullable|string'
        ];
    }

    public function action(Organisation $organisation, Agent $agent, $modelData, $hydratorDelay = 0): OrgAgent
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorDelay;
        $this->initialisation($organisation, $modelData);

        return $this->handle($organisation, $agent, $this->validatedData);
    }


}
