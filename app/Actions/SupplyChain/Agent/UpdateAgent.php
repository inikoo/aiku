<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 18 Jan 2024 17:13:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\Agent;

use App\Actions\GrpAction;
use App\Actions\SupplyChain\Agent\Hydrators\AgentHydrateUniversalSearch;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateSupplyChain;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateProcurement;
use App\Actions\SysAdmin\Organisation\UpdateOrganisation;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Procurement\AgentResource;
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

        UpdateOrganisation::run($agent->organisation, Arr::except($modelData, ['source_id','source_slug','status']));



        $agent = $this->update($agent, Arr::only($modelData, 'status'));
        if ($agent->wasChanged('status')) {

            foreach($agent->organisations as $organisation) {
                OrganisationHydrateProcurement::dispatch($organisation);
            }

            GroupHydrateSupplyChain::run($this->group);
        }

        AgentHydrateUniversalSearch::dispatch($agent);



        return $agent;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->action = true) {
            return true;
        }

        return $request->user()->hasPermissionTo("procurement.".$this->group->id.".edit");
    }

    public function rules(): array
    {
        return [
            'code'         => [
                'sometimes',
                'required',
                'max:9',
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
            'name'         => ['sometimes', 'required', 'string', 'max:255'],
            'email'        => ['nullable', 'email'],
            'phone'        => ['nullable', 'phone:AUTO'],
            'address'      => ['sometimes', 'required', new ValidAddress()],
            'currency_id'  => ['sometimes', 'required', 'exists:currencies,id'],
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
