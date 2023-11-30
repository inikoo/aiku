<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 29 Nov 2023 23:07:51 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Supplier;

use App\Actions\Assets\Currency\SetCurrencyHistoricFields;
use App\Actions\Helpers\Address\StoreAddressAttachToModel;
use App\Actions\Organisation\Group\Hydrators\GroupHydrateProcurement;
use App\Actions\Procurement\Agent\Hydrators\AgentHydrateSuppliers;
use App\Actions\Procurement\Supplier\Hydrators\SupplierHydrateUniversalSearch;
use App\Models\Organisation\Group;
use App\Models\Procurement\Agent;
use App\Models\Procurement\Supplier;
use App\Rules\ValidAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreSupplier
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

    public function handle(Group|Agent $parent, array $modelData, array $addressData = []): Supplier
    {

        if (class_basename($parent) == 'Agent') {
            data_set($modelData, 'group_id', $parent->group_id);
            $group=$parent->group;
        } else {
            $group=$parent;
        }

        /** @var Supplier $supplier */
        $supplier = $parent->suppliers()->create($modelData);
        $supplier->stats()->create();
        SetCurrencyHistoricFields::run($supplier->currency, $supplier->created_at);


        StoreAddressAttachToModel::run($supplier, $addressData, ['scope' => 'contact']);

        $supplier->location = $supplier->getLocation();
        $supplier->save();

        GroupHydrateProcurement::run($group);

        if ($supplier->agent_id) {
            AgentHydrateSuppliers::dispatch($supplier->agent);
        }

        SupplierHydrateUniversalSearch::dispatch($supplier);

        return $supplier;
    }

    public function rules(): array
    {
        return [
            'code'         => ['required', 'unique:suppliers', 'between:2,9', 'alpha'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'email'        => ['nullable', 'email'],
            'phone'        => ['nullable', 'phone:AUTO'],
            'address'      => ['required', new ValidAddress()],
            'currency_id'  => ['required', 'exists:currencies,id'],
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        if (!$this->get('contact_name') and !$this->get('company_name')) {
            $validator->errors()->add('contact_name', 'contact name or company name is required');
        }
    }

    public function action(Group|Agent $parent, $modelData): Supplier
    {
        $this->asAction = true;
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle(
            parent: $parent,
            modelData: Arr::except($validatedData, 'address'),
            addressData: Arr::get($validatedData, 'address')
        );
    }

    public function asController(ActionRequest $request): Supplier
    {
        $this->fillFromRequest($request);
        $request->validate();
        $validatedData = $request->validated();

        return $this->handle(
            parent: group(),
            modelData: Arr::except($validatedData, 'address'),
            addressData: Arr::get($validatedData, 'address')
        );
    }

    public function inAgent(Agent $agent, ActionRequest $request): Supplier
    {
        $this->fillFromRequest($request);
        $request->validate();
        $validatedData = $request->validated();

        return $this->handle(
            parent: $agent,
            modelData: Arr::except($validatedData, 'address'),
            addressData: Arr::get($validatedData, 'address')
        );
    }

    public function htmlResponse(Supplier $supplier): RedirectResponse
    {
        if ($supplier->owner_type == 'Agent') {
            /** @var Agent $agent */
            $agent = $supplier->owner;

            return Redirect::route('procurement.marketplace.agents.show.suppliers.index', $agent->slug);
        }

        return Redirect::route('procurement.marketplace.suppliers.show', $supplier->slug);
    }
}
