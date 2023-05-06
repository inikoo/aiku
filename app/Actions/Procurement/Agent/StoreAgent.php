<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 26 Oct 2022 09:56:46 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Agent;

use App\Actions\Assets\Currency\SetCurrencyHistoricFields;
use App\Actions\Helpers\Address\StoreAddressAttachToModel;
use App\Actions\Procurement\Agent\Hydrators\AgentHydrateUniversalSearch;
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
        $agent = $owner->agents()->create($modelData);
        $agent->stats()->create();
        SetCurrencyHistoricFields::run($agent->currency, $agent->created_at);

        StoreAddressAttachToModel::run($agent, $addressData, ['scope' => 'contact']);
        $agent->location = $agent->getLocation();
        $agent->save();

        foreach($owner->group->tenants as $tenant) {
            $tenant->execute(function () use ($agent) {
                TenantHydrateProcurement::dispatch(app('currentTenant'));
                AgentHydrateUniversalSearch::dispatch($agent);
            });
        }


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
