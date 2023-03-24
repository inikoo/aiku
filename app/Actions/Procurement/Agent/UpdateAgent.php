<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 25 Oct 2022 11:01:21 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Agent;

use App\Actions\Procurement\Agent\Hydrators\AgentHydrateUniversalSearch;
use App\Actions\WithActionUpdate;
use App\Http\Resources\Procurement\AgentResource;
use App\Models\Procurement\Agent;
use Lorisleiva\Actions\ActionRequest;

class UpdateAgent
{
    use WithActionUpdate;

    public function handle(Agent $agent, array $modelData): Agent
    {
        $agent = $this->update($agent, $modelData, ['shared_data','tenant_data', 'settings']);
        AgentHydrateUniversalSearch::dispatch($agent);
        return $agent;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("procurement.edit");
    }
    public function rules(): array
    {
        return [
            'code' => ['sometimes', 'required'],
            'name' => ['sometimes', 'required'],
        ];
    }


    public function asController(Agent $agent, ActionRequest $request): Agent
    {
        $request->validate();
        return $this->handle($agent, $request->all());
    }


    public function jsonResponse(Agent $agent): AgentResource
    {
        return new AgentResource($agent);
    }
}
