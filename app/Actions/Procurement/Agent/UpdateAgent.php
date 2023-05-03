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
use Illuminate\Validation\Validator;

class UpdateAgent
{
    use WithActionUpdate;


    private Agent $agent;

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

    public function action(Agent $agent, $objectData): Agent
    {
        $this->agent=$agent;


        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($agent, $validatedData);
    }

    public function afterValidator(Validator $validator): void
    {

        if($this->agent->owner_id !== app('currentTenant')->id) {
            $validator->errors()->add('agent', 'You can not update the agent.');

        }

    }

    public function asController(Agent $agent, ActionRequest $request): Agent
    {
        $this->agent=$agent;
        $request->validate();
        return $this->handle($agent, $request->all());
    }


    public function jsonResponse(Agent $agent): AgentResource
    {
        return new AgentResource($agent);
    }
}
