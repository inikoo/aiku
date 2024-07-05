<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 18 Jan 2024 17:13:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\Agent;

use App\Actions\GrpAction;
use App\Actions\SupplyChain\Agent\Hydrators\AgentHydrateUniversalSearch;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateAgents;
use App\Actions\SysAdmin\Organisation\StoreOrganisation;
use App\Enums\SysAdmin\Organisation\OrganisationTypeEnum;
use App\Models\SupplyChain\Agent;
use App\Models\SysAdmin\Group;
use App\Rules\IUnique;
use App\Rules\ValidAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;

class StoreAgent extends GrpAction
{
    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("procurement.".$this->group->id.".edit");
    }

    public function handle(Group $group, array $modelData): Agent
    {
        data_set($modelData, 'group_id', $group->id);

        $organisationData = [
            'type'        => OrganisationTypeEnum::AGENT,
            'name'        => Arr::get($modelData, 'name'),
            'code'        => Arr::get($modelData, 'code'),
            'email'       => Arr::get($modelData, 'email'),
            'phone'       => Arr::get($modelData, 'phone'),
            'address'     => Arr::get($modelData, 'address'),
            'currency_id' => Arr::get($modelData, 'currency_id'),
            'group_id'    => Arr::get($modelData, 'group_id', $group->id),
            'country_id'  => Arr::get($modelData, 'country_id', $group->country_id),
            'timezone_id' => Arr::get($modelData, 'timezone_id', $group->timezone_id),
            'language_id' => Arr::get($modelData, 'language_id', $group->language_id)
        ];

        if (Arr::exists($modelData, 'created_at')) {
            $organisationData['created_at'] = Arr::get($modelData, 'created_at');
        }


        $organisation = StoreOrganisation::make()->action(
            $group,
            $organisationData
        );


        /** @var Agent $agent */
        $agent = $organisation->agent()->create(Arr::only($modelData, ['name', 'code', 'created_at', 'source_id', 'source_slug', 'group_id']));
        $agent->stats()->create();


        GroupHydrateAgents::run($group);
        AgentHydrateUniversalSearch::dispatch($agent);


        return $agent;
    }


    public function rules(): array
    {
        // Get the group from the initialisation data
        $group = $this->initialisationData['group'] ?? null;

        // Check if the group is set and if it has an id
        $groupId = $group && $group->id ? $group->id : null;

        return [
            'code'        => [
                'required',
                'max:12',
                'alpha_dash',
                new IUnique(
                    table: 'organisations',
                    extraConditions: [
                        ['column' => 'group_id', 'value' => $this->group->id],
                    ]
                ),
            ],
            'name'        => ['nullable', 'string', 'max:255'],
            'email'       => ['nullable', 'email'],
            'phone'       => ['nullable', 'phone:AUTO'],
            'address'     => ['required', new ValidAddress()],
            'source_id'   => ['sometimes', 'nullable', 'string'],
            'source_slug' => ['sometimes', 'nullable', 'string'],
            'currency_id' => ['required', 'exists:currencies,id'],
            'country_id'  => ['required', 'exists:countries,id'],
            'timezone_id' => ['required', 'exists:timezones,id'],
            'language_id' => ['required', 'exists:languages,id'],
            'deleted_at'  => ['sometimes', 'nullable', 'date'],
        ];
    }


    public function action(Group $group, $modelData): Agent
    {
        $this->asAction = true;
        $this->initialisation($group, $modelData);


        return $this->handle(
            group: $group,
            modelData: $this->validatedData
        );
    }


    public function asController(ActionRequest $request): Agent
    {
        // dd($request->all());
        $this->initialisation(group(), $request);


        return $this->handle(
            group: group(),
            modelData: $this->validatedData
        );
    }

    public function htmlResponse(Agent $agent): RedirectResponse
    {
        return Redirect::route('grp.supply-chain.agents.show', $agent->slug);
    }
}
