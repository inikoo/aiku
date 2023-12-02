<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 29 Nov 2023 23:00:18 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Agent;

use App\Actions\Assets\Currency\SetCurrencyHistoricFields;
use App\Actions\Helpers\Address\StoreAddressAttachToModel;
use App\Actions\Grouping\Group\Hydrators\GroupHydrateProcurement;
use App\Actions\Procurement\Agent\Hydrators\AgentHydrateUniversalSearch;
use App\Models\Grouping\Group;
use App\Models\Procurement\Agent;
use App\Rules\ValidAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreAgent
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

    public function handle(Group $group, array $modelData, array $addressData = []): Agent
    {
        /** @var Agent $agent */
        $agent = $group->agents()->create($modelData);
        $agent->stats()->create();

        SetCurrencyHistoricFields::run($agent->currency, $agent->created_at);
        StoreAddressAttachToModel::run($agent, $addressData, ['scope' => 'contact']);
        $agent->location = $agent->getLocation();
        $agent->save();

        GroupHydrateProcurement::run($group);
        AgentHydrateUniversalSearch::dispatch($agent);


        return $agent;
    }


    public function rules(): array
    {
        return [
            'code'         => ['required', 'unique:agents', 'between:2,9', 'alpha_dash'],
            'contact_name' => ['required', 'string', 'max:255'],
            'company_name' => ['required', 'string', 'max:255'],
            'email'        => ['nullable', 'email'],
            'phone'        => ['nullable', 'phone:AUTO'],
            'address'      => ['required', new ValidAddress()],
            'currency_id'  => ['required', 'exists:currencies,id'],
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        if (!$this->get('contact_name') and !$this->get('company_name')) {
            $validator->errors()->add('company_name', 'contact name or company name required');
        }
    }

    public function action(Group $group, $objectData): Agent
    {
        $this->asAction = true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle(
            group: $group,
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
            group: group(),
            modelData: Arr::except($validatedData, 'address'),
            addressData: Arr::get($validatedData, 'address')
        );
    }

    public function htmlResponse(Agent $agent): RedirectResponse
    {
        return Redirect::route('grp.procurement.marketplace.agents.show', $agent->slug);
    }
}
