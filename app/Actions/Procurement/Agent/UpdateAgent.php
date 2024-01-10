<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 25 Oct 2022 11:01:21 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Agent;

use App\Actions\Helpers\Address\StoreAddressAttachToModel;
use App\Actions\InertiaGroupAction;
use App\Actions\Procurement\Agent\Hydrators\AgentHydrateUniversalSearch;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Procurement\AgentResource;
use App\Models\Procurement\Agent;
use App\Rules\IUnique;
use App\Rules\ValidAddress;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class UpdateAgent extends InertiaGroupAction
{
    use WithActionUpdate;


    private Agent $agent;
    private bool $action = false;

    public function handle(Agent $agent, array $modelData): Agent
    {
        $addressData = Arr::get($modelData, 'address');
        Arr::forget($modelData, 'address');
        $agent = $this->update($agent, $modelData, ['data', 'settings']);
        if ($addressData) {
            StoreAddressAttachToModel::run($agent, $addressData, ['scope' => 'contact']);
            $agent->location = $agent->getLocation();
            $agent->save();
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
                    table: 'agents',
                    extraConditions: [
                        ['column' => 'group_id', 'value' => $this->group->id],
                        [
                            'column'   => 'id',
                            'operator' => '!=',
                            'value'    => $this->agent->id
                        ],
                    ]
                ),
            ],
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
