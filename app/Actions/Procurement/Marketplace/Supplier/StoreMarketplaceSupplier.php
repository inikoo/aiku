<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 25 Jun 2023 13:32:03 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Marketplace\Supplier;

use App\Actions\Assets\Currency\SetCurrencyHistoricFields;
use App\Actions\Helpers\GroupAddress\StoreGroupAddressAttachToModel;
use App\Actions\Procurement\Agent\Hydrators\AgentHydrateSuppliers;
use App\Actions\Procurement\Supplier\Hydrators\SupplierHydrateUniversalSearch;
use App\Actions\Tenancy\Group\Hydrators\GroupHydrateProcurement;
use App\Actions\Tenancy\Tenant\AttachSupplier;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateProcurement;
use App\Enums\Procurement\SupplierTenant\SupplierTenantStatusEnum;
use App\Models\Procurement\Agent;
use App\Models\Procurement\Supplier;
use App\Models\Tenancy\Tenant;
use App\Rules\ValidAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreMarketplaceSupplier
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

    public function handle(Tenant|Agent $owner, ?Agent $agent, array $modelData, array $addressData = []): Supplier
    {
        $modelData['owner_type'] = class_basename($owner);
        $modelData['owner_id']   = $owner->id;


        /** @var Supplier $supplier */
        if ($agent) {

            $supplier = $agent->suppliers()->create($modelData);

            AttachSupplier::run(
                tenant: app('currentTenant'),
                supplier: $supplier,
                pivotData: [
                    'agent_id'   => $agent->id,
                    'status'     => SupplierTenantStatusEnum::OWNER
                ]
            );


        } else {
            $supplier = $owner->mySuppliers()->create($modelData);
            $owner->suppliers()->attach(
                $supplier,
                [
                    'status' => SupplierTenantStatusEnum::OWNER
                ]
            );
        }


        $supplier->stats()->create();
        SetCurrencyHistoricFields::run($supplier->currency, $supplier->created_at);


        StoreGroupAddressAttachToModel::run($supplier, $addressData, ['scope' => 'contact']);

        $supplier->location = $supplier->getLocation();
        $supplier->save();

        TenantHydrateProcurement::dispatch(app('currentTenant'));
        GroupHydrateProcurement::run(app('currentTenant')->group);

        if ($supplier->agent_id) {
            AgentHydrateSuppliers::dispatch($supplier->agent);
        }

        SupplierHydrateUniversalSearch::dispatch($supplier);

        return $supplier;
    }

    public function rules(): array
    {
        return [
            'code'         => ['required', 'unique:group.suppliers', 'between:2,9', 'alpha'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'email'        => ['nullable', 'email'],
            'phone'        => ['nullable', 'phone:AUTO'],
            'address'      => ['required', new ValidAddress()],
            'currency_id'  => ['required', 'exists:central.currencies,id'],
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        if (!$this->get('contact_name') and !$this->get('company_name')) {
            $validator->errors()->add('contact_name', 'contact name or company name is required');
        }
    }

    public function action(Tenant|Agent $owner, ?Agent $agent, $modelData): Supplier
    {
        $this->asAction = true;
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle(
            owner: $owner,
            agent: $agent,
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
            owner: app('currentTenant'),
            agent: null,
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
            owner: app('currentTenant'),
            agent: $agent,
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
