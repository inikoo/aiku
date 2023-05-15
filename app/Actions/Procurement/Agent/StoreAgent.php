<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 26 Oct 2022 09:56:46 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Agent;

use App\Actions\Assets\Currency\SetCurrencyHistoricFields;
use App\Actions\Helpers\GroupAddress\StoreGroupAddressAttachToModel;
use App\Actions\Procurement\Agent\Hydrators\AgentHydrateUniversalSearch;
use App\Actions\Tenancy\Group\Hydrators\GroupHydrateProcurement;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateProcurement;
use App\Models\Procurement\Agent;
use App\Models\Tenancy\Tenant;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreAgent
{
    use AsAction;
    use WithAttributes;

    public function handle(Tenant $owner, array $modelData, array $addressData = []): Agent
    {
        /** @var Agent $agent */
        $agent = $owner->myAgents()->create($modelData);
        $agent->stats()->create();

        $owner->agents()->attach($agent, ['is_owner' => true]);

        SetCurrencyHistoricFields::run($agent->currency, $agent->created_at);

        StoreGroupAddressAttachToModel::run($agent, $addressData, ['scope' => 'contact']);
        $agent->location = $agent->getLocation();
        $agent->save();

        GroupHydrateProcurement::run(app('currentTenant')->group);
        AgentHydrateUniversalSearch::dispatch($agent);
        TenantHydrateProcurement::dispatch(app('currentTenant'));


        return $agent;
    }

    public function rules(): array
    {
        return [
            'code'         => ['required', 'unique:group.agents', 'between:2,9', 'alpha_dash'],
            'name'         => ['required', 'max:250', 'string'],
            'company_name' => ['sometimes', 'required'],
            'contact_name' => ['sometimes', 'required'],
            'email'        => ['sometimes', 'required'],
            'currency_id'  => ['required', 'exists:central.currencies,id'],
        ];
    }

    public function action(Tenant $owner, $objectData, $addressData): Agent
    {
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($owner, $validatedData, $addressData);
    }
}
