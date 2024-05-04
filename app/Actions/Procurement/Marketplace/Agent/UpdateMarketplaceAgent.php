<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 04 May 2024 12:55:09 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Marketplace\Agent;

use App\Actions\SupplyChain\Agent\Hydrators\AgentHydrateUniversalSearch;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Procurement\AgentResource;
use App\Models\SupplyChain\Agent;
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
        $agent = $this->update($agent, $modelData, ['data', 'settings']);
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
            'code'         => ['sometimes', 'required', 'unique:agents', 'between:2,9', 'alpha_dash'],
            'contact_name' => ['sometimes', 'required', 'string', 'max:255'],
            'company_name' => ['sometimes', 'required', 'string', 'max:255'],
            'email'        => ['nullable', 'email'],
            'phone'        => ['nullable', 'phone:AUTO'],
            'address'      => ['sometimes', 'required', new ValidAddress()],
            'currency_id'  => ['sometimes', 'required', 'exists:currencies,id'],
        ];
    }

    public function action(Agent $agent, $modelData): Agent
    {
        $this->agent  =$agent;
        $this->action = true;

        $this->setRawAttributes($modelData);
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
