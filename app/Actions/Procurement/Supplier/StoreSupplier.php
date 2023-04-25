<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 25 Oct 2022 10:53:52 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Procurement\Supplier;

use App\Actions\Assets\Currency\SetCurrencyHistoricFields;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateProcurement;
use App\Actions\Helpers\Address\StoreAddressAttachToModel;
use App\Actions\Procurement\Agent\Hydrators\AgentHydrateSuppliers;
use App\Actions\Procurement\Supplier\Hydrators\SupplierHydrateUniversalSearch;
use App\Models\Tenancy\Tenant;
use App\Models\Procurement\Agent;
use App\Models\Procurement\Supplier;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreSupplier
{
    use AsAction;
    use WithAttributes;

    public function handle(Tenant|Agent $owner, array $modelData, array $addressData = []): Supplier
    {
        if (class_basename($owner) == 'Agent') {
            $modelData['owner_type'] = 'Agent';
            $modelData['owner_id']   = $owner->id;
        }

        /** @var Supplier $supplier */
        $supplier = $owner->suppliers()->create($modelData);
        $supplier->stats()->create();
        SetCurrencyHistoricFields::run($supplier->currency, $supplier->created_at);


        StoreAddressAttachToModel::run($supplier, $addressData, ['scope' => 'contact']);

        $supplier->location = $supplier->getLocation();
        $supplier->save();

        TenantHydrateProcurement::dispatch(app('currentTenant'));
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
            'name'         => ['required', 'max:250', 'string'],
            'company_name' => ['sometimes', 'required'],
            'contact_name' => ['sometimes', 'required'],
            'email'        => ['sometimes', 'required'],
            'currency_id'  => ['required', 'exists:currencies,id'],
            'type'         => ['required', 'in:supplier,sub-supplier']
        ];
    }

    public function action(Tenant|Agent $owner, $objectData): Supplier
    {
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();
        return $this->handle($owner, $validatedData);
    }
}
