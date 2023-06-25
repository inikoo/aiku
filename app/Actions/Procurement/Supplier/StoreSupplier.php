<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 25 Oct 2022 10:53:52 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Supplier;

use App\Actions\Assets\Currency\SetCurrencyHistoricFields;
use App\Actions\Helpers\GroupAddress\StoreGroupAddressAttachToModel;
use App\Actions\Tenancy\Group\Hydrators\GroupHydrateProcurement;
use App\Actions\Tenancy\Tenant\AttachSupplier;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateProcurement;
use App\Actions\Procurement\Agent\Hydrators\AgentHydrateSuppliers;
use App\Actions\Procurement\Supplier\Hydrators\SupplierHydrateUniversalSearch;
use App\Enums\Procurement\SupplierTenant\SupplierTenantStatusEnum;
use App\Models\Tenancy\Tenant;
use App\Models\Procurement\Agent;
use App\Models\Procurement\Supplier;
use Illuminate\Http\RedirectResponse;
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


    public function handle(Tenant|Agent $owner, array $modelData, array $addressData = []): Supplier
    {
        /** @var Supplier $supplier */
        if (class_basename($owner) == 'Agent') {
            $modelData['type']       = 'sub-supplier';
            $modelData['owner_type'] = 'Agent';
            $modelData['owner_id']   = $owner->id;
            $supplier                = $owner->suppliers()->create($modelData);

            AttachSupplier::run(
                tenant: app('currentTenant'),
                supplier: $supplier,
                pivotData: [
                    'type'   => 'sub-supplier',
                    'status' => SupplierTenantStatusEnum::OWNER
                ]
            );
        } else {
            $modelData['type']       = 'supplier';
            $modelData['owner_type'] = 'Agent';
            $supplier                = $owner->mySuppliers()->create($modelData);
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
            'code'         => ['required', 'unique:group.agents', 'between:2,9', 'alpha'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'email'        => ['nullable', 'email'],
            'phone'        => ['nullable', 'phone:AUTO'],
            'currency_id'  => ['required', 'exists:currencies,id'],
        ];
    }

    public function afterValidator(Validator $validator): void
    {
        if (!$this->get('contact_name') and !$this->get('company_name')) {
            $validator->errors()->add('contact_name', 'contact name or company name is required');
        }
    }

    public function action(Tenant|Agent $owner, $objectData): Supplier
    {
        $this->asAction = true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($owner, $validatedData);
    }

    public function asController(ActionRequest $request): Supplier
    {
        $this->fillFromRequest($request);
        $request->validate();

        return $this->handle(app('currentTenant'), $request->validated());
    }

    public function inAgent(Agent $agent, ActionRequest $request): Supplier
    {
        $request->validate();

        return $this->handle($agent, $request->validated());
    }

    public function htmlResponse(Supplier $supplier): RedirectResponse
    {
        if ($supplier->owner_type == 'Agent') {
            return Redirect::route('procurement.marketplace.agents.show.suppliers.index', $supplier->owner->slug);
        }

        return Redirect::route('procurement.marketplace.suppliers.show', $supplier->slug);
    }
}
