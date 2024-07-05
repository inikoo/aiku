<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 18 Jan 2024 17:13:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\Agent;

use App\Actions\GrpAction;
use App\Actions\Procurement\OrgAgent\UpdateOrgAgent;
use App\Actions\SupplyChain\Agent\Hydrators\AgentHydrateUniversalSearch;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateAgents;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydratePurchaseOrders;
use App\Actions\SysAdmin\Organisation\UpdateOrganisation;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\SupplyChain\AgentResource;
use App\Models\SupplyChain\Agent;
use App\Rules\IUnique;
use App\Rules\ValidAddress;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class UpdateAgent extends GrpAction
{
    use WithActionUpdate;


    private Agent $agent;
    private bool $action = false;

    public function handle(Agent $agent, array $modelData): Agent
    {
        UpdateOrganisation::run($agent->organisation, Arr::except($modelData, ['source_id', 'source_slug', 'status']));

        $agent = $this->update($agent, Arr::only($modelData, ['status', 'code', 'name']));
        if ($agent->wasChanged('status')) {
            foreach ($agent->orgAgents as $orgAgent) {
                if (!$agent->status) {
                    UpdateOrgAgent::make()->action($orgAgent, ['status' => false]);
                }
                OrganisationHydratePurchaseOrders::dispatch($orgAgent->organisation);
            }
            GroupHydrateAgents::run($this->group);
        }

        if ($agent->wasChanged(['name', 'code'])) {
            foreach ($agent->orgAgents as $orgAgent) {

                $orgAgent->update(
                    [
                        'code' => $agent->code,
                        'name' => $agent->name
                    ]
                );

            }
        }

        AgentHydrateUniversalSearch::dispatch($agent);


        return $agent;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->action = true) {
            return true;
        }

        return $request->user()->hasPermissionTo("supply-chain.".$this->group->id.".edit");
    }

    public function rules(): array
    {
        return [
            'code'        => [
                'sometimes',
                'required',
                'max:12',
                'alpha_dash',
                new IUnique(
                    table: 'organisations',
                    extraConditions: [
                        ['column' => 'group_id', 'value' => $this->group->id],
                        [
                            'column'   => 'id',
                            'operator' => '!=',
                            'value'    => $this->agent->organisation->id
                        ],
                    ]
                ),
            ],
            'name'        => ['sometimes', 'required', 'string', 'max:255'],
            'email'       => ['nullable', 'email'],
            'phone'       => ['nullable', 'phone:AUTO'],
            'address'     => ['sometimes', 'required', new ValidAddress()],
            'currency_id' => ['sometimes', 'required', 'exists:currencies,id'],
            'country_id'  => ['sometimes', 'required', 'exists:countries,id'],
            'timezone_id' => ['sometimes', 'required', 'exists:timezones,id'],
            'language_id' => ['sometimes', 'required', 'exists:languages,id'],
            'status'      => ['sometimes', 'required', 'boolean'],
        ];
    }

    public function action(Agent $agent, $modelData): Agent
    {
        $this->agent  = $agent;
        $this->action = true;
        $this->initialisation($agent->group, $modelData);

        return $this->handle($agent, $this->validatedData);
    }

    public function asController(Agent $agent, ActionRequest $request): Agent
    {
        $this->agent = $agent;
        $this->initialisation($agent->group, $request);

        return $this->handle($agent, $this->validatedData);
    }


    public function jsonResponse(Agent $agent): AgentResource
    {
        return new AgentResource($agent);
    }
}
