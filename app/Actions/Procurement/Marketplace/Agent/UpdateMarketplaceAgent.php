<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 25 Oct 2022 11:01:21 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Marketplace\Agent;

use App\Actions\Procurement\Agent\Hydrators\AgentHydrateUniversalSearch;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Procurement\AgentResource;
use App\Models\Procurement\Agent;
use App\Rules\ValidAddress;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

class UpdateMarketplaceAgent
{
    use WithActionUpdate;


    private Agent $agent;
    private bool $action = false;

    public function handle(Agent $agent, array $modelData): Agent
    {
        $agent = $this->update($agent, $modelData, ['shared_data','tenant_data', 'settings']);
        AgentHydrateUniversalSearch::dispatch($agent);
        return $agent;
    }

    public function authorize(ActionRequest $request): bool
    {
        if($this->action = true) {
            return true;
        }

        return $request->user()->hasPermissionTo("procurement.edit");
    }

    public function rules(): array
    {
        return [
            'code'         => ['sometimes', 'required', 'unique:group.agents', 'between:2,9', 'alpha_dash'],
            'contact_name' => ['sometimes', 'required', 'string', 'max:255'],
            'company_name' => ['sometimes', 'required', 'string', 'max:255'],
            'email'        => ['nullable', 'email'],
            'phone'        => ['nullable', 'phone:AUTO'],
            'address'      => ['sometimes', 'required', new ValidAddress()],
            'currency_id'  => ['sometimes', 'required', 'exists:central.currencies,id'],
        ];
    }

    public function action(Agent $agent, $objectData): Agent
    {
        $this->agent  =$agent;
        $this->action = true;

        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($agent, $validatedData);
    }

    public function afterValidator(Validator $validator): void
    {

        if($this->agent->owner_id !== app('currentTenant')->id) {
            $validator->errors()->add('code', 'You can not update the agent.');
            $validator->errors()->add('company_name', 'You can not update the agent.');
            $validator->errors()->add('email', 'You can not update the agent.');
            $validator->errors()->add('address', 'You can not update the agent.');
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
