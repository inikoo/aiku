<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 25 Jun 2023 09:32:07 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Marketplace\Agent;

use App\Actions\Assets\Currency\SetCurrencyHistoricFields;
use App\Actions\Helpers\GroupAddress\StoreGroupAddressAttachToModel;
use App\Actions\Procurement\Agent\Hydrators\AgentHydrateUniversalSearch;
use App\Actions\Organisation\Group\Hydrators\GroupHydrateProcurement;
use App\Actions\Organisation\Organisation\Hydrators\OrganisationHydrateProcurement;
use App\Enums\Procurement\AgentOrganisation\AgentOrganisationStatusEnum;
use App\Models\Procurement\Agent;
use App\Models\Organisation\Organisation;
use App\Rules\ValidAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreMarketplaceAgent
{
    use AsAction;
    use WithAttributes;

    private bool $asAction = false;

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }
        return $request->user()->hasPermissionTo("procurement.edit");
    }

    public function handle(Organisation $owner, array $modelData, array $addressData = []): Agent
    {
        $modelData['owner_type'] = 'Organisation';
        /** @var Agent $agent */
        $agent = $owner->myAgents()->create($modelData);
        $agent->stats()->create();

        $owner->agents()->attach(
            $agent,
            ['status' => AgentOrganisationStatusEnum::OWNER]
        );

        SetCurrencyHistoricFields::run($agent->currency, $agent->created_at);

        StoreGroupAddressAttachToModel::run($agent, $addressData, ['scope' => 'contact']);
        $agent->location = $agent->getLocation();
        $agent->save();

        GroupHydrateProcurement::run(app('currentTenant')->group);
        AgentHydrateUniversalSearch::dispatch($agent);
        OrganisationHydrateProcurement::dispatch(app('currentTenant'));


        return $agent;
    }


    public function rules(): array
    {
        return [
            'code'         => ['required', 'unique:group.agents', 'between:2,9', 'alpha_dash'],
            'contact_name' => ['required', 'string', 'max:255'],
            'company_name' => ['required', 'string', 'max:255'],
            'email'        => ['nullable', 'email'],
            'phone'        => ['nullable', 'phone:AUTO'],
            'address'      => ['required', new ValidAddress()],
            'currency_id'  => ['required', 'exists:central.currencies,id'],
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        if (!$this->get('contact_name') and !$this->get('company_name')) {
            $validator->errors()->add('company_name', 'contact name or company name required');
        }
    }

    public function action(Organisation $organisation, $objectData): Agent
    {
        $this->asAction = true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle(
            owner: $organisation,
            modelData: Arr::except($validatedData, 'address'),
            addressData: Arr::get($validatedData, 'address')
        );


    }


    public function asController(ActionRequest $request): Agent
    {
        $this->fillFromRequest($request);
        $request->validate();
        $validatedData=$request->validated();
        return $this->handle(
            owner: app('currentTenant'),
            modelData: Arr::except($validatedData, 'address'),
            addressData: Arr::get($validatedData, 'address')
        );
    }

    public function htmlResponse(Agent $agent): RedirectResponse
    {
        return Redirect::route('procurement.marketplace.agents.show', $agent->slug);
    }
}
